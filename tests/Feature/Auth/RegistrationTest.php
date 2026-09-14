<?php

test('registration screen cannot be rendered when registration is disabled', function () {
    $response = $this->get($this->tenantPath('/register'));

    $response->assertStatus(404);
});

test('users cannot register through the public endpoint', function () {
    $response = $this->post($this->tenantPath('/register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response->assertStatus(404);
});
