<?php

namespace Tests\Feature\Admin;

use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crudAdmin(): User
{
    return User::factory()->create([
        'role' => 'superadmin',
        'name' => 'Super Admin',
        'email' => 'superadmin@test.com',
    ]);
}

test('superadmin can view fakultas list', function () {
    Fakultas::factory()->count(3)->create();

    $response = $this->actingAs(crudAdmin())->get(route('admin.fakultas.index'));

    $response->assertOk();
    $response->assertViewIs('admin.fakultas.index');
    $response->assertViewHas('fakultas');
});

test('superadmin can create fakultas', function () {
    $response = $this->actingAs(crudAdmin())->post(route('admin.fakultas.store'), [
        'nama' => 'Fakultas Teknik Test',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('fakultas', ['nama' => 'Fakultas Teknik Test']);
});

test('superadmin cannot create fakultas with invalid data', function () {
    $this->actingAs(crudAdmin())->post(route('admin.fakultas.store'), [
        'nama' => '',
    ])->assertSessionHasErrors(['nama']);

    $this->assertDatabaseMissing('fakultas', ['nama' => '']);
});

test('superadmin can update fakultas', function () {
    $fakultas = Fakultas::factory()->create(['nama' => 'Old Name']);

    $this->actingAs(crudAdmin())->put(route('admin.fakultas.update', $fakultas), [
        'nama' => 'Updated Name',
    ])->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('fakultas', ['nama' => 'Updated Name']);
    $this->assertDatabaseMissing('fakultas', ['nama' => 'Old Name']);
});

test('superadmin can delete fakultas', function () {
    $fakultas = Fakultas::factory()->create();

    $this->actingAs(crudAdmin())->delete(route('admin.fakultas.destroy', $fakultas))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('fakultas', ['id' => $fakultas->id]);
});

test('admin fakultas cannot manage fakultas', function () {
    $admin = User::factory()->create(['role' => 'admin_fakultas']);

    $this->actingAs($admin)->get(route('admin.fakultas.index'))->assertForbidden();
    $this->actingAs($admin)->post(route('admin.fakultas.store'), ['nama' => 'X'])->assertForbidden();
});

test('superadmin can view prodi list', function () {
    Prodi::factory()->count(3)->create();

    $response = $this->actingAs(crudAdmin())->get(route('admin.prodi.index'));

    $response->assertOk();
    $response->assertViewIs('admin.prodi.index');
    $response->assertViewHas('fakultas');
});

test('superadmin can create prodi', function () {
    $fakultas = Fakultas::factory()->create();

    $this->actingAs(crudAdmin())->post(route('admin.prodi.store'), [
        'fakultas_id' => $fakultas->id,
        'nama' => 'Teknik Informatika Test',
    ])->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('prodi', [
        'nama' => 'Teknik Informatika Test',
        'fakultas_id' => $fakultas->id,
    ]);
});

test('superadmin cannot create prodi with invalid fakultas', function () {
    $this->actingAs(crudAdmin())->post(route('admin.prodi.store'), [
        'fakultas_id' => 999999,
        'nama' => 'Test Prodi',
    ])->assertSessionHasErrors(['fakultas_id']);
});

test('superadmin can update prodi', function () {
    $prodi = Prodi::factory()->create(['nama' => 'Old Prodi']);
    $fakultas = Fakultas::factory()->create();

    $this->actingAs(crudAdmin())->put(route('admin.prodi.update', $prodi), [
        'fakultas_id' => $fakultas->id,
        'nama' => 'Updated Prodi',
    ])->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('prodi', ['nama' => 'Updated Prodi']);
    $this->assertDatabaseMissing('prodi', ['nama' => 'Old Prodi']);
});

test('superadmin can delete prodi', function () {
    $prodi = Prodi::factory()->create();

    $this->actingAs(crudAdmin())->delete(route('admin.prodi.destroy', $prodi))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('prodi', ['id' => $prodi->id]);
});

test('admin fakultas dapat membuat prodi di fakultasnya sendiri', function () {
    $fakultas = Fakultas::factory()->create();
    $admin = User::factory()->create([
        'role' => 'admin_fakultas',
        'fakultas_id' => $fakultas->id,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.prodi.store'), [
        'fakultas_id' => $fakultas->id,
        'nama' => 'Prodi Fakultas Sendiri',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('prodi', ['nama' => 'Prodi Fakultas Sendiri']);
});

test('admin fakultas tidak dapat membuat prodi di fakultas lain', function () {
    $fakultasSendiri = Fakultas::factory()->create();
    $fakultasLain = Fakultas::factory()->create();
    $admin = User::factory()->create([
        'role' => 'admin_fakultas',
        'fakultas_id' => $fakultasSendiri->id,
    ]);

    $this->actingAs($admin)->post(route('admin.prodi.store'), [
        'fakultas_id' => $fakultasLain->id,
        'nama' => 'Prodi Curi',
    ])->assertForbidden();

    $this->assertDatabaseMissing('prodi', ['nama' => 'Prodi Curi']);
});

test('non-admin cannot access admin fakultas routes', function () {
    $mahasiswaUser = User::factory()->create(['role' => 'mahasiswa']);
    $prodi = Prodi::factory()->create();
    Mahasiswa::factory()->create([
        'user_id' => $mahasiswaUser->id,
        'prodi_id' => $prodi->id,
    ]);

    $this->actingAs($mahasiswaUser)->get(route('admin.fakultas.index'))->assertForbidden();
});

test('non-admin cannot access admin prodi routes', function () {
    $mahasiswaUser = User::factory()->create(['role' => 'mahasiswa']);
    $prodi = Prodi::factory()->create();
    Mahasiswa::factory()->create([
        'user_id' => $mahasiswaUser->id,
        'prodi_id' => $prodi->id,
    ]);

    $this->actingAs($mahasiswaUser)->get(route('admin.prodi.index'))->assertForbidden();
});

test('dosen cannot access admin routes', function () {
    $dosenUser = User::factory()->create(['role' => 'dosen']);

    $this->actingAs($dosenUser)->get(route('admin.fakultas.index'))->assertForbidden();
});

test('guest cannot access admin routes', function () {
    $this->get(route('admin.fakultas.index'))->assertRedirect(route('login'));
});
