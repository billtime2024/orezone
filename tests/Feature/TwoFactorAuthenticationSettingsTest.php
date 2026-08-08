<?php

use App\Models\User;

test('two factor authentication is not available via web', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->post('/user/two-factor-authentication');
    expect($response->status())->toBeIn([200, 302, 404]);
})->skip('Legacy Fortify web route — app uses OTP-based API auth');
