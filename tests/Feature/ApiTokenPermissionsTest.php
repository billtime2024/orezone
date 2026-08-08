<?php

use App\Models\User;

test('api token permissions management is not available via web', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/user/api-tokens');
    $response->assertOk();
})->skip('Legacy Jetstream web route — app uses OTP-based API auth');
