<?php

it('redirects to the kampus login page', function () {
    $response = $this->get('/');

    $response->assertRedirect($this->tenantPath('/login'));
});
