<?php

use App\Models\CalonMahasiswa;
use App\Models\Kampus;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createCalon(array $overrides = [], ?Kampus $kampus = null): CalonMahasiswa
{
    $kampus ??= Kampus::first();

    return CalonMahasiswa::create(array_merge([
        'kampus_id' => $kampus->id,
        'no_pendaftar' => 'PMB-'.fake()->unique()->numerify('####'),
        'nama' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'tahun_masuk' => '2026',
    ], $overrides));
}

function adminFakultas(): User
{
    return User::factory()->create([
        'role' => 'admin_fakultas',
        'fakultas_id' => null,
    ]);
}

function superadmin(?Kampus $kampus = null): User
{
    return User::factory()->create([
        'role' => 'superadmin',
        'kampus_id' => $kampus?->id,
    ]);
}

function adminPath(string $path): string
{
    return '/kampus-utama/admin/'.$path;
}

test('admin dapat melihat daftar calon mahasiswa', function () {
    createCalon(['nama' => 'Budi']);
    createCalon(['nama' => 'Citra']);
    createCalon([
        'nama' => 'Terproses',
        'status' => CalonMahasiswa::STATUS_IMPORTED,
    ]);

    $response = $this->actingAs(adminFakultas())->get(adminPath('calon-mahasiswa'));

    $response->assertOk();
    $response->assertViewIs('admin.calon-mahasiswa.index');
    $response->assertSee('Budi');
});

test('admin dapat menerima calon menjadi mahasiswa aktif', function () {
    $kampus = Kampus::factory()->create(['kode' => 'UR', 'slug' => 'ur']);
    $prodi = Prodi::factory()->create(['kampus_id' => $kampus->id, 'nama' => 'Informatika']);
    $calon = createCalon(['tahun_masuk' => '2026'], $kampus);

    $response = $this->actingAs(superadmin($kampus))
        ->post("/ur/admin/calon-mahasiswa/{$calon->id}/terima", [
            'prodi_id' => $prodi->id,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', ['email' => $calon->email, 'role' => 'mahasiswa', 'kampus_id' => $kampus->id]);
    $this->assertDatabaseHas('mahasiswa', [
        'prodi_id' => $prodi->id,
        'angkatan' => '2026',
        'status' => 'aktif',
    ]);
    expect(CalonMahasiswa::find($calon->id)->status)->toBe(CalonMahasiswa::STATUS_IMPORTED);
});

test('nim yang dihasilkan berurut dan diawali kode kampus + angkatan', function () {
    $kampus = Kampus::factory()->create(['kode' => 'UR', 'slug' => 'ur']);
    $prodi = Prodi::factory()->create(['kampus_id' => $kampus->id]);

    $first = createCalon([], $kampus);
    $second = createCalon([], $kampus);

    $me = $this->actingAs(superadmin($kampus));
    $me->post("/ur/admin/calon-mahasiswa/{$first->id}/terima", ['prodi_id' => $prodi->id]);
    $me->post("/ur/admin/calon-mahasiswa/{$second->id}/terima", ['prodi_id' => $prodi->id]);

    $nimList = Mahasiswa::withoutGlobalScope('kampus')->pluck('nim')->sort()->values();

    expect($nimList)->toHaveCount(2);
    expect($nimList[0])->toStartWith('UR2026')
        ->and($nimList[1])->toBe(substr($nimList[0], 0, -5).str_pad((string) ((int) substr($nimList[0], -5) + 1), 5, '0', STR_PAD_LEFT));
});

test('penerimaan tanpa prodi ditolak', function () {
    $calon = createCalon();

    $this->actingAs(adminFakultas())
        ->post(adminPath("calon-mahasiswa/{$calon->id}/terima"), [])
        ->assertSessionHasErrors(['prodi_id']);

    expect(Mahasiswa::count())->toBe(0);
});

test('calon tanpa email wajib diisi manual saat diterima', function () {
    $prodi = Prodi::factory()->create();
    $calon = createCalon(['email' => null]);

    $this->actingAs(adminFakultas())
        ->post(adminPath("calon-mahasiswa/{$calon->id}/terima"), ['prodi_id' => $prodi->id])
        ->assertSessionHasErrors(['email']);

    expect(CalonMahasiswa::find($calon->id)->status)->toBe(CalonMahasiswa::STATUS_PENDING);
});

test('calon yang sudah diproses tidak dapat diproses ulang', function () {
    $prodi = Prodi::factory()->create();
    $calon = createCalon(['status' => CalonMahasiswa::STATUS_IMPORTED]);

    $this->actingAs(adminFakultas())
        ->post(adminPath("calon-mahasiswa/{$calon->id}/terima"), ['prodi_id' => $prodi->id])
        ->assertSessionHasErrors(['error']);

    expect(Mahasiswa::count())->toBe(0);
});

test('admin dapat menolak calon', function () {
    $calon = createCalon();

    $this->actingAs(adminFakultas())
        ->post(adminPath("calon-mahasiswa/{$calon->id}/tolak"))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(CalonMahasiswa::find($calon->id)->status)->toBe(CalonMahasiswa::STATUS_REJECTED)
        ->and(User::count())->toBe(1);
});

test('calon dari kampus lain tidak ditemukan untuk kampus aktif', function () {
    $kampusLain = Kampus::factory()->create();
    $calon = createCalon([], $kampusLain);

    $this->actingAs(adminFakultas())
        ->post(adminPath("calon-mahasiswa/{$calon->id}/terima"), ['prodi_id' => 1])
        ->assertNotFound();
});
