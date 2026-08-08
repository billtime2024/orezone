<?php

use App\Models\User;

test('api token creation is not available via web', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->post('/user/api-tokens', [
        'name' => 'Test Token',
        'permissions' => ['read'],
    ]);
    expect($response->status())->toBeIn([200, 302]);
})->skip('Legacy Jetstream web route — app uses OTP-based API auth');
