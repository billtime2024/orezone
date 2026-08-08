<?php

use App\Models\User;

test('browser sessions management is not available via web', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->delete('/user/other-browser-sessions', [
        'password' => 'password',
    ]);
    expect($response->status())->toBeIn([303, 404]);
})->skip('Legacy Jetstream web route — app uses OTP-based API auth');
