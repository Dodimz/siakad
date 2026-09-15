# SIAKAD Multi-Kampus & API PMB

Dokumen ini menjelaskan arsitektur multi-kampus (SaaS), panel pusat superadmin, dan
integrasi API PMB eksternal.

---

## 1. Ringkasan Arsitektur

- **Satu aplikasi, satu database, banyak kampus.** Tidak memakai paket tenancy seperti
  stancl; isolasi data memakai kolom `kampus_id` di setiap tabel + global scope.
- **Tenant berbasis path**: semua rute aplikasi berpola `/{kampus-slug}/...`,
  mis. `/kampus-utama/login`, `/ur/admin/dashboard`.
- **Panel pusat** superadmin berada di luar prefix kampus: `/central`.
- Setiap request tenant di-resolve oleh middleware `tenant` (`EnsureKampus`) yang:
  1. mencari `Kampus` berdasarkan `{kampus}` di URL,
  2. menolak kampus non-aktif / tidak ditemukan,
  3. mengisi konteks tenant (`TenantManager`) dan `URL::defaults(['kampus' => slug])`.
- **Superadmin boleh mengakses kampus mana pun** tanpa harus cocok `kampus_id`.

### Komponen utama

| Komponen | Lokasi |
|----------|--------|
| Middleware tenant | `app/Http/Middleware/EnsureKampus.php` (alias `tenant`) |
| Middleware panel pusat | `app/Http/Middleware/SetDefaultKampus.php` (alias `set.default.kampus`) |
| Middleware API PMB | `app/Http/Middleware/EnsurePmbToken.php` (alias `auth.pmb`) |
| Konteks tenant | `app/Support/TenantManager.php` |
| Otomatis isi `kampus_id` | `app/Traits/BelongsToTenant.php` |
| Helper URL tenant | `tenant_route()` di `app/Support/helpers.php` |
| Panel pusat | `routes/central/index.php`, `app/Http/Controllers/Central/*` |
| API PMB | `routes/api.php`, `app/Http/Controllers/Api/PmbController.php`, `app/Services/PmbImportService.php` |

---

## 2. Setup Lokal

Prasyarat: PHP 8.4, Composer, Node.js, database (SQLite untuk dev cepat atau MySQL/MariaDB).

```bash
cp .env.example .env
php artisan key:generate
composer install
npm install
```

Konfigurasi database (contoh MySQL/MariaDB XAMPP):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siakad
DB_USERNAME=root
DB_PASSWORD=
```

Migrate + seed:

```bash
php artisan migrate --seed
```

Seeder membuat kampus `kampus-utama` beserta 4 user default:

| Role | Email | Password |
|------|-------|----------|
| Superadmin | `superadmin@siakad.test` | `password` |
| Admin Fakultas | `admin.ftik@siakad.test` | `password` |
| Dosen | `dosen@siakad.test` | `password` |
| Mahasiswa | `mahasiswa@siakad.test` | `password` |

Login: `http://localhost/siakad/kampus-utama/login`.

> Catatan MySQL/MariaDB:
> - Migration `create_kampus_table` harus berjalan **sebelum** migration `add_kampus_id_*`.
>   Jangan memberi timestamp yang sama; gunakan timestamp berurutan.
> - Jangan chain `->index()` setelah `->constrained()` pada definisi foreign key.
>   `constrained()` sudah membuat index; `->index()` pada objek Fluent dapat menghasilkan
>   nama constraint literal `1` yang ditolak MariaDB (errno 121).

---

## 3. Struktur URL & Middleware

| URL | Middleware | Keterangan |
|-----|-----------|------------|
| `/` | — | redirect ke login kampus aktif pertama |
| `/{kampus}/login`, `/{kampus}/dashboard`, `/{kampus}/admin/...` | `tenant`, `auth`, ... | aplikasi tenant |
| `/central`, `/central/kampus` | `set.default.kampus`, `auth`, `role:superadmin` | panel pusat |
| `/api/v1/pmb/calon` | `auth.pmb` | integrasi PMB |
| `/up` | — | health endpoint |

Aturan `{kampus}`: `[a-z0-9-]+` (slug).

Status kampus (`App\Models\Kampus`):

| Status | Konstanta | Warna |
|--------|-----------|-------|
| Trial | `STATUS_TRIAL` | amber |
| Aktif | `STATUS_ACTIVE` | emerald |
| Suspend | `STATUS_SUSPENDED` | red |

Kampus berstatus `suspended` ditolak (redirect ke `/` dengan pesan error), sedangkan
`trial` dan `active` dapat diakses lewat URL tenant.

---

## 4. Panel Pusat Superadmin

Akses: login sebagai user ber-role `superadmin`, lalu buka `/central`.

- `/central` — dashboard agregat: jumlah kampus per status, kesiapan token PMB, total
  user/mahasiswa/dosen, dan statistik calon PMB (total/pending/diterima/ditolak),
  plus rincian per kampus.
- `/central/kampus` — CRUD kampus (nama, slug, kode, kontak, status).

Middleware `set.default.kampus` dijalankan lebih dulu agar pembangkit URL tenant
(termasuk redirect login saat belum terautentikasi) memiliki konteks kampus.

---

## 5. API PMB (v1)

Sistem PMB eksternal dapat **push** data calon mahasiswa ke SIAKAD. Satu token mewakili
satu kampus; kampus ditentukan dari token, bukan dari URL.

### Autentikasi

Kirim token lewat salah satu header berikut:

```
Authorization: Bearer <token>
```

atau

```
X-PMB-Token: <token>
```

Token tidak valid → `401 { "message": "Token tidak valid." }`.

### Endpoint

```
POST /api/v1/pmb/calon
```

Body (JSON):

| Field | Wajib | Tipe | Keterangan |
|-------|:-----:|------|------------|
| `no_pendaftar` | ya | string (max 100) | nomor unik pendaftaran dari PMB |
| `nama` | ya | string (max 255) | nama calon |
| `email` | tidak | email (max 255) | |
| `telepon` | tidak | string (max 30) | |
| `jalur` | tidak | string (max 100) | mis. `snbt`, `reguler`, `beasiswa` |
| `tahun_masuk` | ya | string (max 10) | mis. `2026` |
| `prodi_nama` | tidak | string (max 255) | disimpan sebagai teks, bukan relasi |
| `payload` | tidak | array | data mentah tambahan dari PMB |

Contoh:

```bash
curl -X POST http://localhost/siakad/api/v1/pmb/calon \
  -H "Authorization: Bearer pmb_xxxxxxxx" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "no_pendaftar": "PMB-2026-0001",
    "nama": "Budi Santoso",
    "email": "budi@example.com",
    "telepon": "08123456789",
    "jalur": "snbt",
    "tahun_masuk": "2026",
    "prodi_nama": "Teknik Informatika",
    "payload": { "nilai_raport": 88.5 }
  }'
```

Respons sukses (`201`):

```json
{
  "status": "success",
  "data": { "no_pendaftar": "PMB-2026-0001", "nama": "Budi Santoso", "status": "pending" }
}
```

Respons duplikat (`200`) — idempoten berdasarkan `no_pendaftar` **per kampus**:

```json
{
  "status": "duplicate",
  "data": { "no_pendaftar": "PMB-2026-0001", "nama": "Budi Santoso", "status": "pending" }
}
```

Validasi gagal → `422` dengan format error standar Laravel.

### Status calon mahasiswa

| Status | Nilai | Arti |
|--------|-------|------|
| Menunggu | `pending` | masuk, belum diproses |
| Diterima | `imported` | sudah jadi mahasiswa aktif |
| Ditolak | `rejected` | ditolak admin |

Admin kampus memproses calon di `/{kampus}/admin/calon-mahasiswa`.

### Membuat / merotasi token PMB

Belum tersedia di UI; gunakan tinker:

```bash
php artisan tinker
```

```php
$kampus = App\Models\Kampus::where('slug', 'kampus-utama')->first();

// Terbitkan bila belum ada (token lama dipertahankan).
$kampus->issuePmbToken();

// Rotasi paksa (token lama langsung tidak berlaku).
$kampus->issuePmbToken(force: true);

// Lihat token (kolom pmb_token tersembunyi dari serialisasi default).
$kampus->pmb_token;
```

Simpan token di sisi sistem PMB secara aman; token setara kredensial tulis untuk kampus
tersebut.

---

## 6. Testing

```bash
# Semua test
php artisan test --compact

# Test multi-kampus / panel pusat / PMB
php artisan test --compact tests/Feature/Central/CentralPanelTest.php
php artisan test --compact tests/Feature/Admin/CalonMahasiswaTest.php
php artisan test --compact tests/Feature/Pmb/PmbApiTest.php
```

`tests/TestCase.php` menyiapkan kampus `kampus-utama` dan mengisi konteks tenant untuk
setiap test, serta menyediakan helper `tenantPath()`.
