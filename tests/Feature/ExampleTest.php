<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_root_url_shows_the_public_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Piggery Management')
            ->assertSee(url('/admin/register'), false);
    }
}
