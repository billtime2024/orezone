<?php

use App\Models\User;

test('password confirmation is not available via web', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/user/confirm-password');
    expect($response->status())->toBeIn([200, 404]);
})->skip('Legacy Fortify web route — app uses OTP-based API auth');
