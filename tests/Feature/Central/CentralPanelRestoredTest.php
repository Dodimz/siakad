<?php

use App\Models\CalonMahasiswa;
use App\Models\Kampus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'role' => 'superadmin',
        'name' => 'Super Admin',
        'email' => 'super@test.com',
    ]);
});

test('guests are redirected to a kampus login page', function () {
    $response = $this->get('/central');

    $response->assertRedirect($this->tenantPath('/login'));
});

test('non superadmin cannot access central panel', function () {
    $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

    $response = $this->actingAs($mahasiswa)->get('/central');

    $response->assertForbidden();
});

test('superadmin can view central dashboard', function () {
    $response = $this->actingAs($this->superadmin)->get('/central');

    $response->assertStatus(200);
    $response->assertViewIs('central.dashboard');
    $response->assertViewHas('stats');
    $response->assertViewHas('perKampus');
});

test('dashboard shows aggregated counts across all kampus', function () {
    $kampusLain = Kampus::factory()->create(['slug' => 'kampus-lain']);
    CalonMahasiswa::create([
        'kampus_id' => Kampus::first()->id,
        'no_pendaftar' => 'PMB-A-1',
        'nama' => 'A',
        'tahun_masuk' => '2026',
    ]);
    CalonMahasiswa::create([
        'kampus_id' => $kampusLain->id,
        'no_pendaftar' => 'PMB-B-1',
        'nama' => 'B',
        'tahun_masuk' => '2026',
        'status' => CalonMahasiswa::STATUS_IMPORTED,
    ]);

    $response = $this->actingAs($this->superadmin)->get('/central');

    $response->assertOk();
    $response->assertViewHas('stats', function (array $stats) {
        return $stats['calon_total'] === 2
            && $stats['calon_imported'] === 1
            && isset($stats['calon_pending'], $stats['pmb_ready']);
    });
});

test('perkampus breakdown includes calon count', function () {
    $response = $this->actingAs($this->superadmin)->get('/central');

    $response->assertOk();
    $response->assertViewHas('perKampus', function ($perKampus) {
        return $perKampus->isEmpty() || array_key_exists('calon_mahasiswa_count', $perKampus->first()->getAttributes());
    });
});

test('superadmin can view kampus list', function () {
    Kampus::factory()->count(2)->create();

    $response = $this->actingAs($this->superadmin)->get('/central/kampus');

    $response->assertStatus(200);
    $response->assertViewIs('central.kampus.index');
    $response->assertViewHas('kampus');
});

test('superadmin can create a kampus', function () {
    $response = $this->actingAs($this->superadmin)->post('/central/kampus', [
        'nama' => 'Universitas Test',
        'kode' => 'UT',
        'status' => 'active',
        'email' => 'kampus@test.com',
    ]);

    $response->assertRedirect(route('central.kampus.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('kampus', [
        'nama' => 'Universitas Test',
        'slug' => 'universitas-test',
        'kode' => 'UT',
        'status' => 'active',
    ]);
    expect(Kampus::where('slug', 'universitas-test')->exists())->toBeTrue();
});

test('superadmin cannot create a kampus with duplicate slug', function () {
    Kampus::factory()->create(['slug' => 'universitas-test']);

    $response = $this->actingAs($this->superadmin)->post('/central/kampus', [
        'nama' => 'Universitas Test',
        'slug' => 'universitas-test',
        'kode' => 'UT',
        'status' => 'active',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

test('superadmin can update a kampus', function () {
    $kampus = Kampus::factory()->create(['nama' => 'Kampus Lama']);

    $response = $this->actingAs($this->superadmin)->put("/central/kampus/{$kampus->id}", [
        'nama' => 'Kampus Baru',
        'slug' => 'kampus-baru',
        'kode' => $kampus->kode,
        'status' => 'suspended',
    ]);

    $response->assertRedirect(route('central.kampus.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('kampus', [
        'id' => $kampus->id,
        'nama' => 'Kampus Baru',
        'slug' => 'kampus-baru',
        'status' => 'suspended',
    ]);
});

test('superadmin can delete a kampus', function () {
    $kampus = Kampus::factory()->create();

    $response = $this->actingAs($this->superadmin)->delete("/central/kampus/{$kampus->id}");

    $response->assertRedirect(route('central.kampus.index'));
    $this->assertDatabaseMissing('kampus', ['id' => $kampus->id]);
});
