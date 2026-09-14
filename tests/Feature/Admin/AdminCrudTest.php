<?php

namespace Tests\Feature\Admin;

use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin_fakultas',
        'name' => 'Admin User',
        'email' => 'admin@test.com',
    ]);
});

test('admin can view fakultas list', function () {
    Fakultas::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.fakultas.index'));

    $response->assertStatus(200);
    $response->assertViewIs('admin.fakultas.index');
    $response->assertViewHas('fakultas');
});

test('admin can view create fakultas form', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.fakultas.create'));

    $response->assertStatus(200);
    $response->assertViewIs('admin.fakultas.create');
});

test('admin can create fakultas', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.fakultas.store'), [
        'nama' => 'Fakultas Teknik Test',
        'kode' => 'FT',
    ]);

    $response->assertRedirect(route('admin.fakultas.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('fakultas', [
        'nama' => 'Fakultas Teknik Test',
        'kode' => 'FT',
    ]);
});

test('admin cannot create fakultas with invalid data', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.fakultas.store'), [
        'nama' => '', // Empty name should fail validation
    ]);

    $response->assertSessionHasErrors(['nama']);
    $this->assertDatabaseMissing('fakultas', ['nama' => '']);
});

test('admin can view edit fakultas form', function () {
    $fakultas = Fakultas::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.fakultas.edit', $fakultas));

    $response->assertStatus(200);
    $response->assertViewIs('admin.fakultas.edit');
    $response->assertViewHas('fakultas', $fakultas);
});

test('admin can update fakultas', function () {
    $fakultas = Fakultas::factory()->create(['nama' => 'Old Name']);

    $response = $this->actingAs($this->admin)->put(route('admin.fakultas.update', $fakultas), [
        'nama' => 'Updated Name',
        'kode' => $fakultas->kode,
    ]);

    $response->assertRedirect(route('admin.fakultas.index'));
    $this->assertDatabaseHas('fakultas', ['nama' => 'Updated Name']);
    $this->assertDatabaseMissing('fakultas', ['nama' => 'Old Name']);
});

test('admin can delete fakultas', function () {
    $fakultas = Fakultas::factory()->create();

    $response = $this->actingAs($this->admin)->delete(route('admin.fakultas.destroy', $fakultas));

    $response->assertRedirect(route('admin.fakultas.index'));
    $this->assertDatabaseMissing('fakultas', ['id' => $fakultas->id]);
});

test('admin can view prodi list', function () {
    Prodi::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.prodi.index'));

    $response->assertStatus(200);
    $response->assertViewIs('admin.prodi.index');
    $response->assertViewHas('prodi');
});

test('admin can view create prodi form', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.prodi.create'));

    $response->assertStatus(200);
    $response->assertViewIs('admin.prodi.create');
});

test('admin can create prodi', function () {
    $fakultas = Fakultas::factory()->create();

    $response = $this->actingAs($this->admin)->post(route('admin.prodi.store'), [
        'fakultas_id' => $fakultas->id,
        'nama' => 'Teknik Informatika Test',
        'kode' => 'TI',
        'jenjang' => 'S1',
    ]);

    $response->assertRedirect(route('admin.prodi.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('prodi', [
        'nama' => 'Teknik Informatika Test',
        'fakultas_id' => $fakultas->id,
    ]);
});

test('admin cannot create prodi with invalid fakultas', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.prodi.store'), [
        'fakultas_id' => 999999, // Non-existent fakultas
        'nama' => 'Test Prodi',
        'kode' => 'TP',
    ]);

    $response->assertSessionHasErrors(['fakultas_id']);
});

test('admin can view edit prodi form', function () {
    $prodi = Prodi::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.prodi.edit', $prodi));

    $response->assertStatus(200);
    $response->assertViewIs('admin.prodi.edit');
    $response->assertViewHas('prodi', $prodi);
});

test('admin can update prodi', function () {
    $prodi = Prodi::factory()->create(['nama' => 'Old Prodi']);
    $fakultas = Fakultas::factory()->create();

    $response = $this->actingAs($this->admin)->put(route('admin.prodi.update', $prodi), [
        'fakultas_id' => $fakultas->id,
        'nama' => 'Updated Prodi',
        'kode' => $prodi->kode,
        'jenjang' => $prodi->jenjang,
    ]);

    $response->assertRedirect(route('admin.prodi.index'));
    $this->assertDatabaseHas('prodi', ['nama' => 'Updated Prodi']);
    $this->assertDatabaseMissing('prodi', ['nama' => 'Old Prodi']);
});

test('admin can delete prodi', function () {
    $prodi = Prodi::factory()->create();

    $response = $this->actingAs($this->admin)->delete(route('admin.prodi.destroy', $prodi));

    $response->assertRedirect(route('admin.prodi.index'));
    $this->assertDatabaseMissing('prodi', ['id' => $prodi->id]);
});

test('non-admin cannot access admin fakultas routes', function () {
    $mahasiswaUser = User::factory()->create(['role' => 'mahasiswa']);
    $prodi = Prodi::factory()->create();
    Mahasiswa::factory()->create([
        'user_id' => $mahasiswaUser->id,
        'prodi_id' => $prodi->id,
    ]);

    $response = $this->actingAs($mahasiswaUser)->get(route('admin.fakultas.index'));

    $response->assertStatus(403);
});

test('non-admin cannot access admin prodi routes', function () {
    $mahasiswaUser = User::factory()->create(['role' => 'mahasiswa']);
    $prodi = Prodi::factory()->create();
    Mahasiswa::factory()->create([
        'user_id' => $mahasiswaUser->id,
        'prodi_id' => $prodi->id,
    ]);

    $response = $this->actingAs($mahasiswaUser)->get(route('admin.prodi.index'));

    $response->assertStatus(403);
});

test('dosen cannot access admin routes', function () {
    $dosenUser = User::factory()->create(['role' => 'dosen']);

    $response = $this->actingAs($dosenUser)->get(route('admin.fakultas.index'));

    $response->assertStatus(403);
});

test('guest cannot access admin routes', function () {
    $response = $this->get(route('admin.fakultas.index'));

    $response->assertRedirect(route('login'));
});
