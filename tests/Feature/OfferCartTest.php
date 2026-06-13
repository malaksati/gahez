<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\ProductVariant;
use App\V1\Services\CartItemService;
use App\V1\Services\OfferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesOfferFixtures;
use Tests\TestCase;

class OfferCartTest extends TestCase
{
    use CreatesOfferFixtures;
    use RefreshDatabase;

    protected CartItemService $cartItems;

    protected OfferService $offers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cartItems = app(CartItemService::class);
        $this->offers = app(OfferService::class);
        Carbon::setTestNow('2026-06-15 12:00:00');
    }

    protected function tearDown(): void
    {
        $this->tearDownOfferFixtures();
        parent::tearDown();
    }

    public function test_adding_bogo_product_stores_bonus_quantity_in_cart(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct(['price' => 12]);
        $this->createOffer($product, ['type' => 'bogo', 'value' => 1]);

        $cartItem = $this->cartItems->addOrIncrement($user->id, $product, null, 1);

        $this->assertSame(2, $cartItem->quantity);
    }

    public function test_adding_more_than_max_discounted_quantity_charges_full_price_for_extra_units(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct(['price' => 10, 'discount' => 0]);
        $this->createOffer($product, [
            'type' => 'percentage',
            'value' => 50,
            'max_discounted_quantity' => 2,
        ]);

        $cartItem = $this->cartItems->addOrIncrement($user->id, $product, null, 3);

        $this->assertSame(3, $cartItem->quantity);
        // 2 @ 5.00 + 1 @ 10.00
        $this->assertSame(20.0, $this->cartItems->calculateCartSubtotal($user->id));
    }

    public function test_cart_subtotal_uses_offer_pricing_for_percentage_discount(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct(['price' => 10, 'discount' => 0]);
        $this->createOffer($product, [
            'type' => 'percentage',
            'value' => 50,
            'max_discounted_quantity' => 2,
        ]);

        $this->cartItems->addOrIncrement($user->id, $product, null, 2);

        $this->assertSame(10.0, $this->cartItems->calculateCartSubtotal($user->id));
    }

    public function test_checkout_preview_returns_highest_tier_gift_offer(): void
    {
        $user = $this->createUser();
        $paidProduct = $this->createProduct(['price' => 30]);
        $lowGift = $this->createProduct(['price' => 1]);
        $highGift = $this->createProduct(['price' => 2]);

        $this->createThresholdGiftOffer(20, [$lowGift->id]);
        $highTier = $this->createThresholdGiftOffer(50, [$highGift->id]);

        $this->cartItems->addOrIncrement($user->id, $paidProduct, null, 2);

        $preview = $this->cartItems->getCheckoutPreview($user->id);

        $this->assertSame(60.0, $preview['cart_subtotal']);
        $this->assertNotNull($preview['gift_offer']);
        $this->assertSame($highTier->id, $preview['gift_offer']['id']);
        $this->assertSame($highGift->id, $preview['gift_offer']['reward_products'][0]['id']);
    }

    public function test_bogo_and_threshold_gift_offers_work_together_in_same_cart(): void
    {
        $user = $this->createUser();
        $bogoProduct = $this->createProduct(['price' => 10]);
        $regularProduct = $this->createProduct(['price' => 40]);
        $gift = $this->createProduct(['price' => 1]);

        $this->createOffer($bogoProduct, ['type' => 'bogo', 'value' => 1]);
        $this->createThresholdGiftOffer(50, [$gift->id]);
        $this->createOffer(null, [
            'type' => 'free_delivery',
            'value' => 0,
            'min_cart_amount' => 50,
        ]);

        $this->cartItems->addOrIncrement($user->id, $bogoProduct, null, 1);
        $this->cartItems->addOrIncrement($user->id, $regularProduct, null, 1);

        $subtotal = $this->cartItems->calculateCartSubtotal($user->id);
        $preview = $this->cartItems->getCheckoutPreview($user->id);

        $this->assertSame(50.0, $subtotal);
        $this->assertNotNull($preview['gift_offer']);
        $this->assertTrue($preview['qualifies_for_free_delivery']);

        $bogoLine = CartItem::query()->where('user_id', $user->id)->where('product_id', $bogoProduct->id)->first();
        $this->assertSame(2, $bogoLine->quantity);
    }

    public function test_update_quantity_allows_units_beyond_max_discounted_quantity(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct(['price' => 10, 'discount' => 0]);
        $this->createOffer($product, [
            'type' => 'percentage',
            'value' => 50,
            'max_discounted_quantity' => 5,
        ]);

        $this->cartItems->addOrIncrement($user->id, $product, null, 1);

        $cartItem = $this->cartItems->updateQuantity($user->id, $product, 7);

        $this->assertSame(7, $cartItem->quantity);
        // 5 @ 5.00 + 2 @ 10.00
        $this->assertSame(45.0, $this->cartItems->calculateCartSubtotal($user->id));
    }

    public function test_ten_units_do_not_receive_offer_discount_twice(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct(['price' => 1.75, 'discount' => 0]);
        $this->createOffer($product, [
            'type' => 'percentage',
            'value' => 15,
            'max_discounted_quantity' => 5,
        ]);

        $this->cartItems->addOrIncrement($user->id, $product, null, 10);

        $pricing = $this->offers->calculateLinePricing($product, null, 10);

        $this->assertSame(5, $pricing['discounted_quantity']);
        $this->assertSame(5, $pricing['full_price_quantity']);
        // 5 @ 1.49 + 5 @ 1.75
        $this->assertSame(16.2, $this->cartItems->calculateCartSubtotal($user->id));
    }

    public function test_simple_product_rejects_foreign_variant_id(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct(['price' => 1.75]);
        $otherProduct = $this->createProduct(['price' => 4.75, 'type' => 'variable']);
        $variant = ProductVariant::query()->create([
            'product_id' => $otherProduct->id,
            'sku' => 'OTHER-VAR',
            'slug' => 'other-var',
            'name' => ['en' => 'Other variant', 'ar' => 'متغير آخر'],
            'price' => 4.75,
            'stock' => 10,
            'is_active' => true,
            'is_in_stock' => true,
        ]);

        $this->expectException(ValidationException::class);

        try {
            $this->cartItems->addOrIncrement($user->id, $product, $variant->id, 1);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('variant_id', $exception->errors());

            throw $exception;
        }
    }
}
