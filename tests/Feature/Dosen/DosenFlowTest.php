<?php

namespace Tests\Feature\Dosen;

use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\JadwalKuliah;
use App\Models\Kelas;
use App\Models\KerjaPraktek;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\Skripsi;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create fakultas and prodi first
    $fakultas = Fakultas::factory()->create();
    $this->prodi = Prodi::factory()->create([
        'fakultas_id' => $fakultas->id,
    ]);

    // Create dosen user
    $this->user = User::factory()->create([
        'role' => 'dosen',
        'name' => 'Dr. Test Dosen',
        'email' => 'dosen@test.com',
    ]);

    // Create dosen profile
    $this->dosen = Dosen::factory()->create([
        'user_id' => $this->user->id,
        'prodi_id' => $this->prodi->id,
        'nidn' => '1234567890',
    ]);

    // Create active tahun akademik
    $this->tahunAkademik = TahunAkademik::factory()->create([
        'is_active' => true,
        'tahun' => '2024/2025',
        'semester' => 'Ganjil',
    ]);
});

test('dosen can view their dashboard', function () {
    $response = $this->actingAs($this->user)->get(route('dosen.dashboard'));

    $response->assertStatus(200);
    $response->assertViewIs('dosen.dashboard.index');
});

test('dosen can view their teaching schedule', function () {
    $mataKuliah = MataKuliah::factory()->create([
        'prodi_id' => $this->prodi->id,
    ]);

    $kelas = Kelas::factory()->create([
        'mata_kuliah_id' => $mataKuliah->id,
        'dosen_id' => $this->dosen->id,
        'tahun_akademik_id' => $this->tahunAkademik->id,
    ]);

    JadwalKuliah::factory()->create([
        'kelas_id' => $kelas->id,
        'hari' => 'Senin',
        'jam_mulai' => '08:00',
        'jam_selesai' => '09:40',
    ]);

    $response = $this->actingAs($this->user)->get(route('dosen.dashboard'));

    $response->assertStatus(200);
    $response->assertViewIs('dosen.dashboard.index');
    $response->assertSee($mataKuliah->nama_mk);
});

test('dosen can view bimbingan list', function () {
    $response = $this->actingAs($this->user)->get(route('dosen.bimbingan.index'));

    $response->assertStatus(200);
    $response->assertViewIs('dosen.bimbingan.index');
});

test('dosen can view their bimbingan mahasiswa', function () {
    // Create a mahasiswa with this dosen as wali
    $mahasiswaUser = User::factory()->create(['role' => 'mahasiswa']);
    $mahasiswa = Mahasiswa::factory()->create([
        'user_id' => $mahasiswaUser->id,
        'prodi_id' => $this->prodi->id,
        'dosen_pa_id' => $this->dosen->id,
    ]);

    $response = $this->actingAs($this->user)->get(route('dosen.bimbingan.index'));

    $response->assertStatus(200);
    $response->assertSee($mahasiswa->user->name);
});

test('dosen can view presensi list', function () {
    $response = $this->actingAs($this->user)->get(route('dosen.presensi.index'));

    $response->assertStatus(200);
    $response->assertViewIs('dosen.presensi.index');
});

test('dosen can manage presensi for their class', function () {
    // Create a class
    $mataKuliah = MataKuliah::factory()->create(['prodi_id' => $this->prodi->id]);
    $kelas = Kelas::factory()->create([
        'mata_kuliah_id' => $mataKuliah->id,
        'dosen_id' => $this->dosen->id,
        'tahun_akademik_id' => $this->tahunAkademik->id,
    ]);

    $response = $this->actingAs($this->user)->get(route('dosen.presensi.kelas', $kelas));

    $response->assertStatus(200);
    $response->assertViewIs('dosen.presensi.kelas');
});

test('dosen can view skripsi bimbingan list', function () {
    $response = $this->actingAs($this->user)->get(route('dosen.skripsi.index'));

    $response->assertStatus(200);
    $response->assertViewIs('dosen.skripsi.index');
});

test('dosen can view their skripsi bimbingan', function () {
    // Create mahasiswa and skripsi
    $mahasiswaUser = User::factory()->create(['role' => 'mahasiswa']);
    $mahasiswa = Mahasiswa::factory()->create([
        'user_id' => $mahasiswaUser->id,
        'prodi_id' => $this->prodi->id,
    ]);

    $skripsi = Skripsi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'judul' => 'Test Skripsi',
        'pembimbing1_id' => $this->dosen->id,
        'status' => 'bimbingan',
    ]);

    $response = $this->actingAs($this->user)->get(route('dosen.skripsi.index'));

    $response->assertStatus(200);
    $response->assertSee($skripsi->judul);
});

test('dosen can view kp bimbingan list', function () {
    $response = $this->actingAs($this->user)->get(route('dosen.kp.index'));

    $response->assertStatus(200);
    $response->assertViewIs('dosen.kp.index');
});

test('dosen can view their kp bimbingan', function () {
    // Create mahasiswa and KP
    $mahasiswaUser = User::factory()->create(['role' => 'mahasiswa']);
    $mahasiswa = Mahasiswa::factory()->create([
        'user_id' => $mahasiswaUser->id,
        'prodi_id' => $this->prodi->id,
    ]);

    $kp = KerjaPraktek::create([
        'mahasiswa_id' => $mahasiswa->id,
        'judul_laporan' => 'Test KP',
        'pembimbing_id' => $this->dosen->id,
        'nama_perusahaan' => 'Test Company',
        'tanggal_mulai' => now()->toDateString(),
        'tanggal_selesai' => now()->addMonths(3)->toDateString(),
        'status' => 'berlangsung',
    ]);

    $response = $this->actingAs($this->user)->get(route('dosen.kp.index'));

    $response->assertStatus(200);
    $response->assertSee($kp->nama_perusahaan);
});

test('dosen cannot access mahasiswa routes', function () {
    $response = $this->actingAs($this->user)->get(route('mahasiswa.dashboard'));

    $response->assertStatus(403);
});

test('dosen cannot access admin routes', function () {
    $response = $this->actingAs($this->user)->get(route('admin.fakultas.index'));

    $response->assertStatus(403);
});

test('dosen can only view their own classes', function () {
    // Create another dosen
    $otherDosenUser = User::factory()->create(['role' => 'dosen']);
    $otherDosen = Dosen::factory()->create([
        'user_id' => $otherDosenUser->id,
        'prodi_id' => $this->prodi->id,
    ]);

    // Create class for other dosen
    $mataKuliah = MataKuliah::factory()->create(['prodi_id' => $this->prodi->id]);
    $otherKelas = Kelas::factory()->create([
        'mata_kuliah_id' => $mataKuliah->id,
        'dosen_id' => $otherDosen->id,
        'tahun_akademik_id' => $this->tahunAkademik->id,
    ]);

    // This dosen should not be able to access other dosen's class
    $response = $this->actingAs($this->user)->get(route('dosen.presensi.kelas', $otherKelas));

    $response->assertStatus(403);
});

test('dosen can view nilai input form for their class', function () {
    // Create a class
    $mataKuliah = MataKuliah::factory()->create(['prodi_id' => $this->prodi->id]);
    $kelas = Kelas::factory()->create([
        'mata_kuliah_id' => $mataKuliah->id,
        'dosen_id' => $this->dosen->id,
        'tahun_akademik_id' => $this->tahunAkademik->id,
    ]);

    $response = $this->actingAs($this->user)->get(route('dosen.penilaian.show', $kelas));

    $response->assertStatus(200);
    $response->assertViewIs('dosen.penilaian.show');
});

test('guest cannot access dosen routes', function () {
    $response = $this->get(route('dosen.dashboard'));

    $response->assertRedirect(route('login'));
});
