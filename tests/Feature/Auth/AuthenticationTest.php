<?php

namespace Tests\Feature\Auth;

use App\Models\Kampus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = $this->get($this->tenantPath('/login'));

    $response->assertStatus(200);
    $response->assertViewIs('auth.login');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->post($this->tenantPath('/login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('mahasiswa.dashboard'));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->post($this->tenantPath('/login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors();
});

test('superadmin global (kampus_id null) dapat login di kampus mana pun', function () {
    $superadmin = User::factory()->create([
        'password' => Hash::make('password'),
        'role' => 'superadmin',
        'kampus_id' => null,
    ]);

    $response = $this->post($this->tenantPath('/login'), [
        'email' => $superadmin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($superadmin);
    $response->assertRedirect(route('admin.dashboard'));
});

test('pengguna milik kampus lain tidak dapat login di kampus ini', function () {
    $kampusLain = Kampus::factory()->create(['slug' => 'kampus-b']);

    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'kampus_id' => $kampusLain->id,
    ]);

    $response = $this->post($this->tenantPath('/login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors(['email']);
});

test('users can not authenticate with invalid email', function () {
    $response = $this->post($this->tenantPath('/login'), [
        'email' => 'nonexistent@test.com',
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors(['email']);
});

test('login requires email', function () {
    $response = $this->post($this->tenantPath('/login'), [
        'email' => '',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('login requires password', function () {
    $user = User::factory()->create();

    $response = $this->post($this->tenantPath('/login'), [
        'email' => $user->email,
        'password' => '',
    ]);

    $response->assertSessionHasErrors(['password']);
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->assertAuthenticated();

    $response = $this->post($this->tenantPath('/logout'));

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('authenticated users are redirected from login page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get($this->tenantPath('/login'));

    $response->assertRedirect(route('dashboard'));
});

test('users cannot view registration page when registration is disabled', function () {
    $response = $this->get($this->tenantPath('/register'));

    $response->assertStatus(404);
});

test('users cannot register when registration is disabled', function () {
    $response = $this->post($this->tenantPath('/register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response->assertStatus(404);
});
