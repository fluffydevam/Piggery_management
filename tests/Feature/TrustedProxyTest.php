<?php

namespace Tests\Feature;

use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    public function test_forwarded_proto_header_produces_https_urls(): void
    {
        $this->withHeader('X-Forwarded-Proto', 'https')
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get('/admin/login')
            ->assertOk();

        $this->assertStringStartsWith('https://', url('/admin'));
        $this->assertStringStartsWith('https://', asset('css/filament/filament/app.css'));
    }

    public function test_requests_without_the_header_stay_on_http(): void
    {
        $this->get('/admin/login')->assertOk();

        $this->assertStringStartsWith('http://', url('/admin'));
    }
}
