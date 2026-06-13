<?php

namespace Tests\Feature;

use App\Models\Slider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SliderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_sliders_index_filters_by_type(): void
    {
        Slider::query()->create([
            'image' => 'sliders/home.jpg',
            'type' => Slider::TYPE_HOME,
        ]);

        Slider::query()->create([
            'image' => 'sliders/offer.jpg',
            'type' => Slider::TYPE_OFFER,
        ]);

        $homeResponse = $this->getJson('/api/v1/sliders?type=home');
        $homeResponse->assertOk();
        $homeResponse->assertJsonCount(1, 'data');
        $homeResponse->assertJsonPath('data.0.type', 'home');

        $offerResponse = $this->getJson('/api/v1/sliders?type=offer');
        $offerResponse->assertOk();
        $offerResponse->assertJsonCount(1, 'data');
        $offerResponse->assertJsonPath('data.0.type', 'offer');
    }

    public function test_sliders_index_accepts_all_page_types(): void
    {
        foreach (Slider::types() as $type) {
            Slider::query()->create([
                'image' => "sliders/{$type}.jpg",
                'type' => $type,
            ]);

            $this->getJson('/api/v1/sliders?type='.$type)
                ->assertOk()
                ->assertJsonPath('data.0.type', $type);
        }
    }

    public function test_sliders_index_rejects_invalid_type(): void
    {
        $this->getJson('/api/v1/sliders?type=invalid')
            ->assertStatus(422);
    }
}
