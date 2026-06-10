<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreConfigApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_config_returns_theme_defaults(): void
    {
        Setting::query()->updateOrCreate(['key' => 'store_primary_color'], ['value' => '#112233', 'type' => 'string']);
        setting_forget('store_primary_color');

        $response = $this->getJson('/api/v1/store/config');

        $response->assertOk()
            ->assertJsonPath('data.theme.primary_color', '#112233')
            ->assertJsonStructure([
                'data' => [
                    'app_name',
                    'currency',
                    'logo_url',
                    'theme' => [
                        'primary_color',
                        'secondary_color',
                        'category_layout',
                        'product_layout',
                        'font_family',
                    ],
                ],
            ]);
    }

}
