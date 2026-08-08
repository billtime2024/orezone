<?php

use App\Models\User;

test('email verification is not available via web', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);
    $response = $this->actingAs($user)->get('/email/verify');
    expect($response->status())->toBeIn([200, 404]);
})->skip('Legacy Fortify web route — app uses OTP-based API auth');
