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
        'kampus_id' => app(TenantManager::class)->id(),
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success');

    $admin = User::withoutGlobalScope('kampus')->where('email', 'admin.kampus@siakad.test')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->role)->toBe('admin_kampus')
        ->and($admin->kampus_id)->toBe(app(TenantManager::class)->id());
});

test('superadmin dapat menunjuk admin kampus untuk kampus lain', function () {
    $superadmin = User::factory()->create([
        'role' => 'superadmin',
        'kampus_id' => null,
    ]);

    $kampusB = \App\Models\Kampus::factory()->create(['slug' => 'kampus-b']);

    $response = $this->actingAs($superadmin)->post($this->tenantPath('/admin/users'), [
        'name' => 'Admin Kampus B',
        'email' => 'admin.kampus.b@siakad.test',
        'password' => 'password123',
        'role' => 'admin_kampus',
        'kampus_id' => $kampusB->id,
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success');

    $admin = User::withoutGlobalScope('kampus')->where('email', 'admin.kampus.b@siakad.test')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->kampus_id)->toBe($kampusB->id);
});

test('admin kampus wajib menyebutkan kampus tujuan', function () {
    $superadmin = User::factory()->create([
        'role' => 'superadmin',
        'kampus_id' => null,
    ]);

    $response = $this->actingAs($superadmin)->post($this->tenantPath('/admin/users'), [
        'name' => 'Admin Tanpa Kampus',
        'email' => 'admin.nonkampus@siakad.test',
        'password' => 'password123',
        'role' => 'admin_kampus',
    ]);

    $response->assertSessionHasErrors(['kampus_id']);
});

test('admin kampus dapat diganti kampusnya melalui edit', function () {
    $superadmin = User::factory()->create([
        'role' => 'superadmin',
        'kampus_id' => null,
    ]);

    $admin = User::factory()->create([
        'role' => 'admin_kampus',
        'kampus_id' => app(TenantManager::class)->id(),
    ]);

    $kampusC = \App\Models\Kampus::factory()->create(['slug' => 'kampus-c']);

    $response = $this->actingAs($superadmin)->put($this->tenantPath('/admin/users/'.$admin->id), [
        'name' => $admin->name,
        'email' => $admin->email,
        'role' => 'admin_kampus',
        'kampus_id' => $kampusC->id,
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success');

    expect($admin->fresh()->kampus_id)->toBe($kampusC->id);
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
