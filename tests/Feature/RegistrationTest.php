<?php

test('registration route redirects to coming-soon', function () {
    $response = $this->get('/register');

    $response->assertRedirect('/coming-soon');
});
