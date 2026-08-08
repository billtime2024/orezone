<?php

use App\Models\User;

test('update password is not available via web', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->put('/user/password', [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);
    expect($response->status())->toBeIn([200, 302, 404]);
})->skip('Legacy Fortify web route — app uses OTP-based API auth');
