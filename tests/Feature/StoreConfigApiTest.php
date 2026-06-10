<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreConfigApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_config_returns_app_branding(): void
    {
        $response = $this->getJson('/api/v1/store/config');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'app_name',
                    'currency',
                    'logo_url',
                ],
            ])
            ->assertJsonMissing(['data' => ['theme']]);
    }
}
