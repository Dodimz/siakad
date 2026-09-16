<?php

use App\Models\Kampus;
use App\Models\PmbImportLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function pmbLog(array $overrides = [], ?Kampus $kampus = null): PmbImportLog
{
    $kampus ??= Kampus::first();

    return PmbImportLog::create(array_merge([
        'kampus_id' => $kampus->id,
        'no_pendaftar' => 'PMB-'.fake()->unique()->numerify('####'),
        'nama' => fake()->name(),
        'aksi' => PmbImportLog::AKSI_CREATED,
    ], $overrides));
}

function pmbAdmin(): User
{
    return User::factory()->create([
        'role' => 'admin_fakultas',
        'kampus_id' => null,
    ]);
}

test('admin dapat melihat log import PMB kampusnya', function () {
    pmbLog(['no_pendaftar' => 'PMB-A-1', 'nama' => 'Andi']);
    pmbLog(['no_pendaftar' => 'PMB-A-2', 'nama' => 'Budi']);

    $response = $this->actingAs(pmbAdmin())->get('/kampus-utama/admin/pmb-logs');

    $response->assertOk();
    $response->assertViewIs('admin.pmb-logs.index');
    $response->assertViewHas('logs', fn ($logs) => $logs->total() === 2);
    $response->assertSee('Andi');
});

test('log dari kampus lain tidak tampil untuk admin kampus aktif', function () {
    $kampusLain = Kampus::factory()->create(['slug' => 'kampus-lain']);
    pmbLog(['no_pendaftar' => 'PMB-A-1', 'nama' => 'Tampil']);
    pmbLog(['no_pendaftar' => 'PMB-B-1', 'nama' => 'Sembunyi'], $kampusLain);

    $response = $this->actingAs(pmbAdmin())->get('/kampus-utama/admin/pmb-logs');

    $response->assertOk();
    $response->assertViewHas('logs', fn ($logs) => $logs->total() === 1);
    $response->assertSee('Tampil');
    $response->assertDontSee('Sembunyi');
});

test('superadmin dapat melihat log import PMB seluruh kampus di central', function () {
    $kampusLain = Kampus::factory()->create(['slug' => 'kampus-lain']);
    pmbLog(['no_pendaftar' => 'PMB-A-1', 'nama' => 'Andi']);
    pmbLog(['no_pendaftar' => 'PMB-B-1', 'nama' => 'Budi'], $kampusLain);

    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $response = $this->actingAs($superadmin)->get('/central/pmb-logs');

    $response->assertOk();
    $response->assertViewIs('central.pmb-logs.index');
    $response->assertViewHas('logs', fn ($logs) => $logs->total() === 2);
    $response->assertSee('Andi');
    $response->assertSee('Budi');
});
