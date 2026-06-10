<?php

namespace Tests\Support;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Offer;
use App\Models\OfferRewardProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;

trait CreatesOfferFixtures
{
    protected static int $fixtureSequence = 0;

    protected function nextFixtureId(): int
    {
        return ++self::$fixtureSequence;
    }

    protected function createBrand(): Brand
    {
        $id = $this->nextFixtureId();

        return Brand::query()->create([
            'name' => ['en' => "Brand {$id}", 'ar' => "ماركة {$id}"],
        ]);
    }

    protected function createProduct(array $attributes = [], ?Brand $brand = null): Product
    {
        $id = $this->nextFixtureId();
        $brand ??= $this->createBrand();

        return Product::query()->create(array_merge([
            'type' => 'simple',
            'sku' => "SKU-{$id}",
            'slug' => "product-{$id}",
            'name' => ['en' => "Product {$id}", 'ar' => "منتج {$id}"],
            'description' => ['en' => 'Description', 'ar' => 'وصف'],
            'price' => 10.00,
            'stock' => 100,
            'discount' => 0,
            'discount_type' => null,
            'is_active' => true,
            'is_approved' => true,
            'is_bookable' => true,
            'brand_id' => $brand->id,
        ], $attributes));
    }

    protected function createCategory(array $attributes = []): Category
    {
        $id = $this->nextFixtureId();

        return Category::query()->create(array_merge([
            'name' => ['en' => "Category {$id}", 'ar' => "فئة {$id}"],
            'is_active' => true,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function createOffer(Product|Category|null $offerable, array $attributes = []): Offer
    {
        $id = $this->nextFixtureId();

        $defaults = [
            'name' => ['en' => "Offer {$id}", 'ar' => "عرض {$id}"],
            'type' => 'percentage',
            'value' => 10,
            'min_cart_amount' => null,
            'max_discounted_quantity' => null,
            'ends_when_out_of_stock' => false,
            'start_date' => null,
            'end_date' => null,
            'is_active' => true,
            'offerable_id' => $offerable?->id,
            'offerable_type' => $offerable ? $offerable::class : null,
        ];

        $payload = array_merge($defaults, $attributes);

        if (($payload['type'] ?? null) === 'bogo') {
            $payload = array_merge([
                'value' => 0,
                'bogo_buy_quantity' => 1,
                'bogo_bonus_quantity' => max(1, (int) ($payload['bogo_bonus_quantity'] ?? $payload['value'] ?? 1)),
                'bogo_bonus_discount_type' => 'percentage',
                'bogo_bonus_discount_value' => 100,
            ], $payload);
        }

        return Offer::query()->create($payload);
    }

    /**
     * @param  list<int>  $giftProductIds
     */
    protected function createThresholdGiftOffer(float $minCartAmount, array $giftProductIds, array $attributes = []): Offer
    {
        $offer = $this->createOffer(null, array_merge([
            'type' => 'threshold_gift',
            'value' => 1,
            'min_cart_amount' => $minCartAmount,
        ], $attributes));

        foreach (array_values($giftProductIds) as $index => $productId) {
            OfferRewardProduct::query()->create([
                'offer_id' => $offer->id,
                'product_id' => $productId,
                'sort_order' => $index + 1,
            ]);
        }

        return $offer->fresh('rewardProducts.product');
    }

    protected function createUser(): User
    {
        $id = $this->nextFixtureId();

        return User::factory()->create([
            'email' => "user{$id}@example.com",
            'role' => 'user',
        ]);
    }

    protected function freezeOfferWindow(?Carbon $start = null, ?Carbon $end = null): void
    {
        Carbon::setTestNow($start ?? Carbon::parse('2026-06-15 12:00:00'));

        if ($end) {
            // no-op: callers pass explicit start/end on offers
        }
    }

    protected function tearDownOfferFixtures(): void
    {
        Carbon::setTestNow();
    }
}
