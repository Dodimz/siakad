<?php

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
