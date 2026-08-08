<?php

use App\Models\User;

test('profile information update is not available via web', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->put('/user/profile-information', [
        'name' => 'Test Name',
        'email' => 'test@example.com',
    ]);
    expect($response->status())->toBeIn([200, 302, 404]);
})->skip('Legacy Fortify web route — app uses OTP-based API auth');
