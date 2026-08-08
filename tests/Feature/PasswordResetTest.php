<?php

test('password reset link screen can be rendered', function () {
    $response = $this->get('/forgot-password');
    $response->assertOk();
});
