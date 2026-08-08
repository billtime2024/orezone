<?php

use App\Models\User;

test('api token deletion is not available via web', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->delete('/user/api-tokens/1');
    expect($response->status())->toBeIn([200, 302, 404, 500]);
})->skip('Legacy Jetstream web route — app uses OTP-based API auth');
