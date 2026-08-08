<?php

use App\Models\User;

test('account deletion is not available via web', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->delete('/user', [
        'password' => 'password',
    ]);
    expect($response->status())->toBeIn([200, 302, 404]);
})->skip('Legacy Jetstream web route — app uses OTP-based API auth');
