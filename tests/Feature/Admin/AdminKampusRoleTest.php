<?php

use App\Models\User;
use App\Support\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('superadmin dapat membuat user admin kampus', function () {
    $superadmin = User::factory()->create([
        'role' => 'superadmin',
        'kampus_id' => null,
    ]);

    $response = $this->actingAs($superadmin)->post($this->tenantPath('/admin/users'), [
        'name' => 'Admin Kampus A',
        'email' => 'admin.kampus@siakad.test',
        'password' => 'password123',
        'role' => 'admin_kampus',
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success');

    $admin = User::withoutGlobalScope('kampus')->where('email', 'admin.kampus@siakad.test')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->role)->toBe('admin_kampus')
        ->and($admin->kampus_id)->toBe(app(TenantManager::class)->id());
});

test('admin kampus dapat mengakses dashboard admin', function () {
    $admin = User::factory()->create(['role' => 'admin_kampus']);

    $response = $this->actingAs($admin)->get($this->tenantPath('/admin/dashboard'));

    $response->assertOk();
});

test('admin kampus tidak dapat mengelola user', function () {
    $admin = User::factory()->create(['role' => 'admin_kampus']);

    $response = $this->actingAs($admin)->get($this->tenantPath('/admin/users'));

    $response->assertForbidden();
});

test('role admin_kampus ditolak oleh middleware bagi non-admin', function () {
    $dosen = User::factory()->create(['role' => 'dosen']);

    $response = $this->actingAs($dosen)->get($this->tenantPath('/admin/dashboard'));

    $response->assertForbidden();
});
