<?php

namespace Tests\Feature;

use Tests\TestCase;

class HttpsEnforcementTest extends TestCase
{
    public function test_http_requests_are_redirected_to_https_when_enforced(): void
    {
        config()->set('app.force_https', true);

        $this->get('http://localhost/platform-login')
            ->assertRedirect('https://localhost/platform-login');
    }

    public function test_https_requests_are_served_normally_when_enforced(): void
    {
        config()->set('app.force_https', true);

        $this->get('https://localhost/platform-login')
            ->assertOk();
    }
}
