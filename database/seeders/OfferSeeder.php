<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        $apples = Product::query()->where('sku', 'APPLE-1KG')->first();
        $milk = Product::query()->where('sku', 'MILK-1L')->first();

        if ($apples) {
            Offer::query()->updateOrCreate(
                [
                    'offerable_type' => Product::class,
                    'offerable_id' => $apples->id,
                    'type' => 'percentage',
                ],
                [
                    'name' => ['en' => '15% off Apples', 'ar' => 'خصم ١٥٪ على التفاح'],
                    'value' => 15,
                    'max_discounted_quantity' => 5,
                    'is_active' => true,
                    'show_countdown' => false,
                    'start_date' => now()->subDay(),
                    'end_date' => now()->addMonths(3),
                ],
            );
        }

        if ($milk) {
            Offer::query()->updateOrCreate(
                [
                    'offerable_type' => Product::class,
                    'offerable_id' => $milk->id,
                    'type' => 'bogo',
                ],
                [
                    'name' => ['en' => 'Buy 1 Get 1 Milk', 'ar' => 'اشتري ١ واحصل على ١ حليب'],
                    'value' => 0,
                    'bogo_buy_quantity' => 1,
                    'bogo_bonus_quantity' => 1,
                    'bogo_bonus_discount_type' => 'percentage',
                    'bogo_bonus_discount_value' => 100,
                    'is_active' => true,
                    'show_countdown' => false,
                    'start_date' => now()->subDay(),
                    'end_date' => now()->addMonths(2),
                ],
            );
        }
    }
}
