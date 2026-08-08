<?php

use App\Models\User;

test('users cannot authenticate with invalid credentials', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('login route redirects to coming-soon', function () {
    $response = $this->get('/login');
    $response->assertRedirect('/coming-soon');
});
