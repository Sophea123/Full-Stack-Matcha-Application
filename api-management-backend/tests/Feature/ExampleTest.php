<?php

test('the application redirects to the admin dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/admin');
});
