<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\JadwalKuliah;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Nilai;
use App\Models\Prodi;
use App\Models\Ruangan;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SeedKampusBackfill::class);

        $this->call(RolePermissionSeeder::class);

        // ==========================================
        // 1. SUPERADMIN
        // ==========================================
        User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@siakad.test',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);

        // ==========================================
        // 2. TAHUN AKADEMIK
        // ==========================================
        TahunAkademik::create([
            'tahun' => '2023/2024',
            'semester' => 'Ganjil',
            'is_active' => false,
            'tanggal_mulai' => '2023-09-01',
            'tanggal_selesai' => '2024-01-31',
        ]);

        $taLalu = TahunAkademik::create([
            'tahun' => '2023/2024',
            'semester' => 'Genap',
            'is_active' => false,
            'tanggal_mulai' => '2024-02-01',
            'tanggal_selesai' => '2024-06-30',
        ]);

        $taAktif = TahunAkademik::create([
            'tahun' => '2024/2025',
            'semester' => 'Ganjil',
            'is_active' => true,
            'tanggal_mulai' => '2024-09-01',
            'tanggal_selesai' => '2025-01-31',
        ]);

        // ==========================================
        // 3. FAKULTAS
        // ==========================================
        $fakultas = Fakultas::create(['nama' => 'Fakultas Teknik dan Ilmu Komputer']);

        // Admin Fakultas
        $adminFakultas = User::create([
            'name' => 'Admin FTIK',
            'email' => 'admin.ftik@siakad.test',
            'password' => Hash::make('password'),
            'role' => 'admin_fakultas',
            'fakultas_id' => $fakultas->id,
        ]);

        // ==========================================
        // 4. PROGRAM STUDI
        // ==========================================
        $prodi = Prodi::create([
            'nama' => 'Teknik Informatika',
            'fakultas_id' => $fakultas->id,
        ]);

        // ==========================================
        // 5. RUANGAN
        // ==========================================
        $ruangan = Ruangan::create([
            'kode_ruangan' => 'LC-01',
            'nama_ruangan' => 'Lab Komputer 1',
            'kapasitas' => 40,
            'gedung' => 'Gedung A',
            'lantai' => 1,
        ]);

        // ==========================================
        // 6. MATA KULIAH - KURIKULUM 8 SEMESTER (144 SKS)
        // ==========================================
        $kurikulum = $this->getKurikulum($prodi->id);

        foreach ($kurikulum as $mk) {
            MataKuliah::create($mk);
        }

        // ==========================================
        // 7. DOSEN
        // ==========================================
        $dosenUser = User::create([
            'name' => 'Dr. Ahmad Fauzi, M.Kom.',
            'email' => 'dosen@siakad.test',
            'password' => Hash::make('password'),
            'role' => 'dosen',
        ]);

        $dosen = Dosen::create([
            'user_id' => $dosenUser->id,
            'nidn' => '0012056701',
            'prodi_id' => $prodi->id,
        ]);

        // ==========================================
        // 8. KELAS (untuk semester 1-2)
        // ==========================================
        $mataKuliahSem1 = MataKuliah::where('semester', 1)->get();
        $mataKuliahSem2 = MataKuliah::where('semester', 2)->get();

        $kelasList = [];
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $jamMulai = ['08:00', '10:00', '13:00', '15:00'];

        $dayIndex = 0;
        $jamIndex = 0;

        foreach ($mataKuliahSem1->merge($mataKuliahSem2) as $mk) {
            $kelas = Kelas::create([
                'mata_kuliah_id' => $mk->id,
                'dosen_id' => $dosen->id,
                'nama_kelas' => 'A',
                'kapasitas' => 40,
                'tahun_akademik_id' => $taAktif->id,
            ]);

            // Create jadwal
            JadwalKuliah::create([
                'kelas_id' => $kelas->id,
                'hari' => $hari[$dayIndex % 5],
                'jam_mulai' => $jamMulai[$jamIndex % 4],
                'jam_selesai' => date('H:i', strtotime($jamMulai[$jamIndex % 4]) + 5400), // +1.5 hours
                'ruangan' => $ruangan->nama_ruangan,
            ]);

            $kelasList[] = $kelas;
            $dayIndex++;
            $jamIndex++;
        }

        // ==========================================
        // 9. MAHASISWA (Semester 5 - Angkatan 2022)
        // ==========================================
        $mahasiswaUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'mahasiswa@siakad.test',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
        ]);

        $mahasiswa = Mahasiswa::create([
            'user_id' => $mahasiswaUser->id,
            'nim' => '2022101001',
            'prodi_id' => $prodi->id,
            'angkatan' => 2022,
            'dosen_pa_id' => $dosen->id,
            'status' => 'aktif',
        ]);

        // ==========================================
        // 10. RIWAYAT AKADEMIK (4 Semester Selesai)
        // ==========================================

        // Buat tahun akademik untuk semester 1-4
        $ta2022Ganjil = TahunAkademik::create([
            'tahun' => '2022/2023', 'semester' => 'Ganjil', 'is_active' => false,
            'tanggal_mulai' => '2022-09-01', 'tanggal_selesai' => '2023-01-31',
        ]);
        $ta2022Genap = TahunAkademik::create([
            'tahun' => '2022/2023', 'semester' => 'Genap', 'is_active' => false,
            'tanggal_mulai' => '2023-02-01', 'tanggal_selesai' => '2023-06-30',
        ]);

        // Semester 1 (20 SKS - 7 MK)
        $krs1 = Krs::create(['mahasiswa_id' => $mahasiswa->id, 'tahun_akademik_id' => $ta2022Ganjil->id, 'status' => 'approved']);
        foreach (MataKuliah::where('semester', 1)->get() as $mk) {
            $kelas = Kelas::where('mata_kuliah_id', $mk->id)->first();
            if ($kelas) {
                KrsDetail::create(['krs_id' => $krs1->id, 'kelas_id' => $kelas->id]);
                $nilaiAngka = rand(75, 92);
                Nilai::create(['mahasiswa_id' => $mahasiswa->id, 'kelas_id' => $kelas->id, 'nilai_angka' => $nilaiAngka, 'nilai_huruf' => $this->convertToLetter($nilaiAngka)]);
            }
        }

        // Semester 2 (20 SKS - 7 MK)
        $krs2 = Krs::create(['mahasiswa_id' => $mahasiswa->id, 'tahun_akademik_id' => $ta2022Genap->id, 'status' => 'approved']);
        foreach (MataKuliah::where('semester', 2)->get() as $mk) {
            $kelas = Kelas::where('mata_kuliah_id', $mk->id)->first();
            if ($kelas) {
                KrsDetail::create(['krs_id' => $krs2->id, 'kelas_id' => $kelas->id]);
                $nilaiAngka = rand(73, 90);
                Nilai::create(['mahasiswa_id' => $mahasiswa->id, 'kelas_id' => $kelas->id, 'nilai_angka' => $nilaiAngka, 'nilai_huruf' => $this->convertToLetter($nilaiAngka)]);
            }
        }

        // Semester 3 (21 SKS - 7 MK) - 2023/2024 Ganjil (sudah ada di atas)
        $ta2023Ganjil = TahunAkademik::where('tahun', '2023/2024')->where('semester', 'Ganjil')->first();
        $krs3 = Krs::create(['mahasiswa_id' => $mahasiswa->id, 'tahun_akademik_id' => $ta2023Ganjil->id, 'status' => 'approved']);
        foreach (MataKuliah::where('semester', 3)->get() as $mk) {
            // Create kelas for semester 3
            $kelas3 = Kelas::create([
                'mata_kuliah_id' => $mk->id, 'dosen_id' => $dosen->id, 'nama_kelas' => 'A',
                'kapasitas' => 40, 'tahun_akademik_id' => $ta2023Ganjil->id,
            ]);
            KrsDetail::create(['krs_id' => $krs3->id, 'kelas_id' => $kelas3->id]);
            $nilaiAngka = rand(72, 88);
            Nilai::create(['mahasiswa_id' => $mahasiswa->id, 'kelas_id' => $kelas3->id, 'nilai_angka' => $nilaiAngka, 'nilai_huruf' => $this->convertToLetter($nilaiAngka)]);
        }

        // Semester 4 (21 SKS - 7 MK) - 2023/2024 Genap
        $krs4 = Krs::create(['mahasiswa_id' => $mahasiswa->id, 'tahun_akademik_id' => $taLalu->id, 'status' => 'approved']);
        foreach (MataKuliah::where('semester', 4)->get() as $mk) {
            $kelas4 = Kelas::create([
                'mata_kuliah_id' => $mk->id, 'dosen_id' => $dosen->id, 'nama_kelas' => 'A',
                'kapasitas' => 40, 'tahun_akademik_id' => $taLalu->id,
            ]);
            KrsDetail::create(['krs_id' => $krs4->id, 'kelas_id' => $kelas4->id]);
            $nilaiAngka = rand(74, 90);
            Nilai::create(['mahasiswa_id' => $mahasiswa->id, 'kelas_id' => $kelas4->id, 'nilai_angka' => $nilaiAngka, 'nilai_huruf' => $this->convertToLetter($nilaiAngka)]);
        }

        // ==========================================
        // 11. KRS SEMESTER 5 SEKARANG (Draft)
        // ==========================================
        $krsSekarang = Krs::create([
            'mahasiswa_id' => $mahasiswa->id,
            'tahun_akademik_id' => $taAktif->id,
            'status' => 'draft',
        ]);

        // Create kelas untuk semester 5 dan ambil KRS
        foreach (MataKuliah::where('semester', 5)->get() as $mk) {
            $kelas5 = Kelas::create([
                'mata_kuliah_id' => $mk->id, 'dosen_id' => $dosen->id, 'nama_kelas' => 'A',
                'kapasitas' => 40, 'tahun_akademik_id' => $taAktif->id,
            ]);
            KrsDetail::create(['krs_id' => $krsSekarang->id, 'kelas_id' => $kelas5->id]);
        }

        // ==========================================
        // OUTPUT
        // ==========================================
        $this->command->newLine();
        $this->command->info('✅ Database seeded successfully!');
        $this->command->newLine();
        $this->command->info('📋 Login Credentials:');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Superadmin', 'superadmin@siakad.test', 'password'],
                ['Admin Fakultas', 'admin.ftik@siakad.test', 'password'],
                ['Dosen', 'dosen@siakad.test', 'password'],
                ['Mahasiswa', 'mahasiswa@siakad.test', 'password'],
            ]
        );
        $this->command->newLine();
        $this->command->info("📚 Kurikulum: {$prodi->nama}");
        $this->command->info('   Total: 144 SKS | 8 Semester | '.MataKuliah::count().' Mata Kuliah');
    }

    /**
     * Kurikulum Teknik Informatika - 8 Semester - 144 SKS
     */
    private function getKurikulum(int $prodiId): array
    {
        return [
            // ====== SEMESTER 1 (20 SKS) ======
            ['kode_mk' => 'TI101', 'nama_mk' => 'Algoritma dan Pemrograman I', 'sks' => 4, 'semester' => 1, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI102', 'nama_mk' => 'Matematika Diskrit', 'sks' => 3, 'semester' => 1, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI103', 'nama_mk' => 'Pengantar Teknologi Informasi', 'sks' => 3, 'semester' => 1, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI104', 'nama_mk' => 'Kalkulus I', 'sks' => 3, 'semester' => 1, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI105', 'nama_mk' => 'Fisika Dasar', 'sks' => 3, 'semester' => 1, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI106', 'nama_mk' => 'Bahasa Inggris I', 'sks' => 2, 'semester' => 1, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI107', 'nama_mk' => 'Pendidikan Pancasila', 'sks' => 2, 'semester' => 1, 'prodi_id' => $prodiId],

            // ====== SEMESTER 2 (20 SKS) ======
            ['kode_mk' => 'TI201', 'nama_mk' => 'Algoritma dan Pemrograman II', 'sks' => 4, 'semester' => 2, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI202', 'nama_mk' => 'Struktur Data', 'sks' => 4, 'semester' => 2, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI203', 'nama_mk' => 'Kalkulus II', 'sks' => 3, 'semester' => 2, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI204', 'nama_mk' => 'Aljabar Linear', 'sks' => 3, 'semester' => 2, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI205', 'nama_mk' => 'Bahasa Inggris II', 'sks' => 2, 'semester' => 2, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI206', 'nama_mk' => 'Pendidikan Kewarganegaraan', 'sks' => 2, 'semester' => 2, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI207', 'nama_mk' => 'Praktikum Algoritma', 'sks' => 2, 'semester' => 2, 'prodi_id' => $prodiId],

            // ====== SEMESTER 3 (20 SKS) ======
            ['kode_mk' => 'TI301', 'nama_mk' => 'Pemrograman Berorientasi Objek', 'sks' => 4, 'semester' => 3, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI302', 'nama_mk' => 'Basis Data', 'sks' => 4, 'semester' => 3, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI303', 'nama_mk' => 'Sistem Operasi', 'sks' => 3, 'semester' => 3, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI304', 'nama_mk' => 'Statistika dan Probabilitas', 'sks' => 3, 'semester' => 3, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI305', 'nama_mk' => 'Organisasi dan Arsitektur Komputer', 'sks' => 3, 'semester' => 3, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI306', 'nama_mk' => 'Praktikum Basis Data', 'sks' => 2, 'semester' => 3, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI307', 'nama_mk' => 'Agama', 'sks' => 2, 'semester' => 3, 'prodi_id' => $prodiId],

            // ====== SEMESTER 4 (20 SKS) ======
            ['kode_mk' => 'TI401', 'nama_mk' => 'Pemrograman Web', 'sks' => 4, 'semester' => 4, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI402', 'nama_mk' => 'Jaringan Komputer', 'sks' => 4, 'semester' => 4, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI403', 'nama_mk' => 'Rekayasa Perangkat Lunak', 'sks' => 3, 'semester' => 4, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI404', 'nama_mk' => 'Interaksi Manusia dan Komputer', 'sks' => 3, 'semester' => 4, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI405', 'nama_mk' => 'Analisis dan Perancangan Sistem', 'sks' => 3, 'semester' => 4, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI406', 'nama_mk' => 'Praktikum Jaringan', 'sks' => 2, 'semester' => 4, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI407', 'nama_mk' => 'Etika Profesi', 'sks' => 2, 'semester' => 4, 'prodi_id' => $prodiId],

            // ====== SEMESTER 5 (20 SKS) ======
            ['kode_mk' => 'TI501', 'nama_mk' => 'Pemrograman Mobile', 'sks' => 4, 'semester' => 5, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI502', 'nama_mk' => 'Kecerdasan Buatan', 'sks' => 3, 'semester' => 5, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI503', 'nama_mk' => 'Keamanan Sistem Informasi', 'sks' => 3, 'semester' => 5, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI504', 'nama_mk' => 'Sistem Terdistribusi', 'sks' => 3, 'semester' => 5, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI505', 'nama_mk' => 'Manajemen Proyek TI', 'sks' => 3, 'semester' => 5, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI506', 'nama_mk' => 'Praktikum Mobile', 'sks' => 2, 'semester' => 5, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI507', 'nama_mk' => 'Kewirausahaan', 'sks' => 2, 'semester' => 5, 'prodi_id' => $prodiId],

            // ====== SEMESTER 6 (18 SKS) ======
            ['kode_mk' => 'TI601', 'nama_mk' => 'Machine Learning', 'sks' => 3, 'semester' => 6, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI602', 'nama_mk' => 'Data Mining', 'sks' => 3, 'semester' => 6, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI603', 'nama_mk' => 'Cloud Computing', 'sks' => 3, 'semester' => 6, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI604', 'nama_mk' => 'Pengolahan Citra Digital', 'sks' => 3, 'semester' => 6, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI605', 'nama_mk' => 'Metodologi Penelitian', 'sks' => 2, 'semester' => 6, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI606', 'nama_mk' => 'Kerja Praktek', 'sks' => 4, 'semester' => 6, 'prodi_id' => $prodiId],

            // ====== SEMESTER 7 (14 SKS) ======
            ['kode_mk' => 'TI701', 'nama_mk' => 'Internet of Things', 'sks' => 3, 'semester' => 7, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI702', 'nama_mk' => 'Big Data Analytics', 'sks' => 3, 'semester' => 7, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI703', 'nama_mk' => 'Natural Language Processing', 'sks' => 3, 'semester' => 7, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI704', 'nama_mk' => 'Proyek 1 (Proposal Skripsi)', 'sks' => 2, 'semester' => 7, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI705', 'nama_mk' => 'Pilihan 1', 'sks' => 3, 'semester' => 7, 'prodi_id' => $prodiId],

            // ====== SEMESTER 8 (12 SKS) ======
            ['kode_mk' => 'TI801', 'nama_mk' => 'Deep Learning', 'sks' => 3, 'semester' => 8, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI802', 'nama_mk' => 'Pilihan 2', 'sks' => 3, 'semester' => 8, 'prodi_id' => $prodiId],
            ['kode_mk' => 'TI803', 'nama_mk' => 'Skripsi', 'sks' => 6, 'semester' => 8, 'prodi_id' => $prodiId],
        ];
    }

    private function convertToLetter(int $nilai): string
    {
        return match (true) {
            $nilai >= 85 => 'A',
            $nilai >= 80 => 'A-',
            $nilai >= 75 => 'B+',
            $nilai >= 70 => 'B',
            $nilai >= 65 => 'C+',
            $nilai >= 60 => 'C',
            $nilai >= 55 => 'D',
            default => 'E',
        };
    }
}
