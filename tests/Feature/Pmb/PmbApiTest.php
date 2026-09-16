<?php

use App\Models\CalonMahasiswa;
use App\Models\Kampus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function pmbHeaders(string $token): array
{
    return ['Authorization' => 'Bearer '.$token, 'Accept' => 'application/json'];
}

test('token yang valid dapat mengirim calon mahasiswa', function () {
    $kampus = Kampus::factory()->withPmbToken()->create();

    $response = $this->postJson('/api/v1/pmb/calon', [
        'no_pendaftar' => 'PMB-2026-001',
        'nama' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'telepon' => '08123456789',
        'jalur' => 'snbt',
        'tahun_masuk' => '2026',
        'prodi_nama' => 'Teknik Informatika',
    ], pmbHeaders($kampus->pmb_token));

    $response->assertCreated();
    $response->assertJson([
        'status' => 'success',
        'data' => ['no_pendaftar' => 'PMB-2026-001', 'nama' => 'Budi Santoso', 'status' => 'pending'],
    ]);

    $this->assertDatabaseHas('calon_mahasiswa', [
        'kampus_id' => $kampus->id,
        'no_pendaftar' => 'PMB-2026-001',
        'tahun_masuk' => '2026',
        'status' => 'pending',
    ]);

    $this->assertDatabaseHas('pmb_import_logs', [
        'kampus_id' => $kampus->id,
        'no_pendaftar' => 'PMB-2026-001',
        'nama' => 'Budi Santoso',
        'aksi' => 'created',
    ]);
});

test('request tanpa token ditolak', function () {
    $this->postJson('/api/v1/pmb/calon', [])
        ->assertUnauthorized()
        ->assertJson(['message' => 'Token tidak valid.']);
});

test('request dengan token salah ditolak', function () {
    $this->postJson('/api/v1/pmb/calon', [], pmbHeaders('pmb_salah'))
        ->assertUnauthorized();
});

test('no pendaftar yang sama per kampus menghasilkan duplicate, bukan baris baru', function () {
    $kampus = Kampus::factory()->withPmbToken()->create();
    $payload = [
        'no_pendaftar' => 'PMB-2026-001',
        'nama' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'tahun_masuk' => '2026',
    ];

    $this->postJson('/api/v1/pmb/calon', $payload, pmbHeaders($kampus->pmb_token))->assertCreated();
    $this->postJson('/api/v1/pmb/calon', $payload, pmbHeaders($kampus->pmb_token))
        ->assertOk()
        ->assertJson(['status' => 'duplicate']);

    expect(CalonMahasiswa::count())->toBe(1);

    $this->assertDatabaseHas('pmb_import_logs', [
        'kampus_id' => $kampus->id,
        'no_pendaftar' => 'PMB-2026-001',
        'aksi' => 'created',
    ]);
    $this->assertDatabaseHas('pmb_import_logs', [
        'kampus_id' => $kampus->id,
        'no_pendaftar' => 'PMB-2026-001',
        'aksi' => 'duplicate',
    ]);
});

test('no pendaftar sama di kampus berbeda diperbolehkan', function () {
    $kampusA = Kampus::factory()->withPmbToken()->create();
    $kampusB = Kampus::factory()->withPmbToken()->create();
    $payload = ['no_pendaftar' => 'PMB-2026-001', 'nama' => 'Budi Santoso', 'tahun_masuk' => '2026'];

    $this->postJson('/api/v1/pmb/calon', $payload, pmbHeaders($kampusA->pmb_token))->assertCreated();
    $this->postJson('/api/v1/pmb/calon', $payload, pmbHeaders($kampusB->pmb_token))->assertCreated();

    expect(CalonMahasiswa::forKampus($kampusA)->count())->toBe(1)
        ->and(CalonMahasiswa::forKampus($kampusB)->count())->toBe(1);
});

test('validasi menolak payload tidak lengkap', function () {
    $kampus = Kampus::factory()->withPmbToken()->create();

    $this->postJson('/api/v1/pmb/calon', ['nama' => 'Tanpa No Pendaftar'], pmbHeaders($kampus->pmb_token))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['no_pendaftar', 'tahun_masuk']);

    $this->postJson('/api/v1/pmb/calon', [
        'no_pendaftar' => 'PMB-2026-002',
        'nama' => 'Budi',
        'tahun_masuk' => '2026',
        'email' => 'bukan-email',
    ], pmbHeaders($kampus->pmb_token))->assertJsonValidationErrors(['email']);
});

test('data mentah payload ikut tersimpan', function () {
    $kampus = Kampus::factory()->withPmbToken()->create();

    $this->postJson('/api/v1/pmb/calon', [
        'no_pendaftar' => 'PMB-2026-003',
        'nama' => 'Citra',
        'tahun_masuk' => '2026',
        'payload' => ['nik' => '1234567890123456', 'asal_sekolah' => 'SMA 1'],
    ], pmbHeaders($kampus->pmb_token))->assertCreated();

    $calon = CalonMahasiswa::first();
    expect($calon->payload)->toBeArray()
        ->toMatchArray(['nik' => '1234567890123456', 'asal_sekolah' => 'SMA 1']);
});

test('query calon tetaplah ter-scope per kampus', function () {
    $kampusA = Kampus::factory()->withPmbToken()->create();
    $kampusB = Kampus::factory()->withPmbToken()->create();

    CalonMahasiswa::create([
        'kampus_id' => $kampusA->id,
        'no_pendaftar' => 'PMB-A-1',
        'nama' => 'A',
        'tahun_masuk' => '2026',
    ]);
    CalonMahasiswa::create([
        'kampus_id' => $kampusB->id,
        'no_pendaftar' => 'PMB-B-1',
        'nama' => 'B',
        'tahun_masuk' => '2026',
    ]);

    expect(CalonMahasiswa::withoutGlobalScope('kampus')->count())->toBe(2)
        ->and(CalonMahasiswa::forKampus($kampusA)->count())->toBe(1)
        ->and(CalonMahasiswa::forKampus($kampusB)->count())->toBe(1);
});

test('issuePmbToken membuat token baru dan tidak mengubahnya tanpa force', function () {
    $kampus = Kampus::factory()->create();

    $token = $kampus->issuePmbToken();

    expect($token)->toBeString()->toStartWith('pmb_')
        ->and(strlen($token))->toBeGreaterThan(16)
        ->and($kampus->fresh()->pmb_token)->toBe($token);

    expect($kampus->issuePmbToken())->toBe($token);

    $tokenBaru = $kampus->issuePmbToken(true);
    expect($tokenBaru)->not->toBe($token);
});

test('perintah kampus:pmb-token menerbitkan token', function () {
    $kampus = Kampus::factory()->create(['slug' => 'kampus-test']);

    $this->artisan('kampus:pmb-token', ['kampus' => 'kampus-test'])
        ->expectsOutputToContain('kampus-test')
        ->assertExitCode(0);

    expect(Kampus::find($kampus->id)->pmb_token)->toStartWith('pmb_');
});

test('perintah kampus:pmb-token menolak slug tak dikenal', function () {
    $this->artisan('kampus:pmb-token', ['kampus' => 'tidak-ada'])
        ->expectsOutputToContain('tidak ditemukan')
        ->assertExitCode(1);
});
