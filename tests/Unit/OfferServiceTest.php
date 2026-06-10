<?php

namespace Tests\Unit;

use App\V1\Services\OfferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\Support\CreatesOfferFixtures;
use Tests\TestCase;

class OfferServiceTest extends TestCase
{
    use CreatesOfferFixtures;
    use RefreshDatabase;

    protected OfferService $offers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->offers = app(OfferService::class);
        Carbon::setTestNow('2026-06-15 12:00:00');
    }

    protected function tearDown(): void
    {
        $this->tearDownOfferFixtures();
        parent::tearDown();
    }

    public function test_bogo_expands_total_quantity_with_free_units(): void
    {
        $product = $this->createProduct(['price' => 10]);
        $this->createOffer($product, ['type' => 'bogo', 'value' => 1]);

        $this->assertSame(1, $this->offers->bogoFreeQuantityPerPaidUnit($product));
        $this->assertSame(2, $this->offers->totalQuantityWithBogoBonus($product, 1));
        $this->assertSame(4, $this->offers->totalQuantityWithBogoBonus($product, 2));
    }

    public function test_bogo_billable_quantity_counts_full_price_units_only(): void
    {
        $product = $this->createProduct();
        $this->createOffer($product, ['type' => 'bogo', 'value' => 1]);

        $pricingTwo = $this->offers->calculateBogoSubtotal(
            $this->offers->resolveProductBogoOfferForProduct($product),
            10,
            2,
        );
        $pricingFour = $this->offers->calculateBogoSubtotal(
            $this->offers->resolveProductBogoOfferForProduct($product),
            10,
            4,
        );

        $this->assertSame(1, $pricingTwo['full_price_units']);
        $this->assertSame(2, $pricingFour['full_price_units']);
    }

    public function test_bogo_line_pricing_charges_only_full_price_units(): void
    {
        $product = $this->createProduct(['price' => 10]);
        $this->createOffer($product, ['type' => 'bogo', 'value' => 1]);

        $pricing = $this->offers->calculateLinePricing($product, null, 2);

        $this->assertSame(1, $pricing['full_price_quantity']);
        $this->assertSame(10.0, $pricing['line_subtotal']);
        $this->assertSame(5.0, $pricing['unit_price']);
    }

    public function test_bogo_buy_two_get_one_half_price(): void
    {
        $product = $this->createProduct(['price' => 10]);
        $this->createOffer($product, [
            'type' => 'bogo',
            'bogo_buy_quantity' => 2,
            'bogo_bonus_quantity' => 1,
            'bogo_bonus_discount_type' => 'percentage',
            'bogo_bonus_discount_value' => 50,
        ]);

        $pricing = $this->offers->calculateLinePricing($product, null, 3);

        $this->assertSame(25.0, $pricing['line_subtotal']);
        $this->assertSame(2, $pricing['full_price_quantity']);
        $this->assertSame(1, $pricing['discounted_quantity']);
    }

    public function test_category_bogo_applies_on_single_cart_line(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct(['price' => 3.5]);
        $product->categories()->attach($category->id);

        $this->createOffer($category, [
            'type' => 'bogo',
            'bogo_buy_quantity' => 1,
            'bogo_bonus_quantity' => 1,
            'bogo_bonus_discount_type' => 'percentage',
            'bogo_bonus_discount_value' => 50,
        ]);

        $cartItems = collect([
            (object) ['id' => 1, 'product' => $product->fresh('categories'), 'variant' => null, 'quantity' => 2],
        ]);

        $linePricings = $this->offers->calculateCartLinePricings($cartItems);

        $this->assertSame(5.25, $linePricings[1]['line_subtotal']);
        $this->assertSame(1, $linePricings[1]['full_price_quantity']);
        $this->assertSame(1, $linePricings[1]['discounted_quantity']);
    }

    public function test_category_bogo_applies_across_products_in_root_tree(): void
    {
        $root = $this->createCategory();
        $child = $this->createCategory(['parent_id' => $root->id]);
        $water = $this->createProduct(['price' => 1]);
        $cola = $this->createProduct(['price' => 2]);
        $water->categories()->attach($child->id);
        $cola->categories()->attach($root->id);

        $this->createOffer($root, [
            'type' => 'bogo',
            'bogo_buy_quantity' => 2,
            'bogo_bonus_quantity' => 1,
            'bogo_bonus_discount_type' => 'percentage',
            'bogo_bonus_discount_value' => 50,
        ]);

        $cartItems = collect([
            (object) ['id' => 1, 'product' => $water->fresh('categories'), 'variant' => null, 'quantity' => 2],
            (object) ['id' => 2, 'product' => $cola->fresh('categories'), 'variant' => null, 'quantity' => 1],
        ]);

        $linePricings = $this->offers->calculateCartLinePricings($cartItems);

        $this->assertSame(2.0, $linePricings[1]['line_subtotal']);
        $this->assertSame(1.0, $linePricings[2]['line_subtotal']);
        $this->assertSame(3.0, $linePricings[1]['line_subtotal'] + $linePricings[2]['line_subtotal']);
    }

    public function test_percentage_discount_respects_max_discounted_quantity_in_pricing(): void
    {
        $product = $this->createProduct(['price' => 10, 'discount' => 0]);
        $this->createOffer($product, [
            'type' => 'percentage',
            'value' => 50,
            'max_discounted_quantity' => 2,
        ]);

        $pricing = $this->offers->calculateLinePricing($product, null, 2);

        $this->assertSame(2, $pricing['discounted_quantity']);
        $this->assertSame(10.0, $pricing['line_subtotal']);
    }

    public function test_percentage_discount_caps_discounted_units_only_beyond_max_is_full_price(): void
    {
        $product = $this->createProduct(['price' => 10, 'discount' => 0]);
        $this->createOffer($product, [
            'type' => 'percentage',
            'value' => 50,
            'max_discounted_quantity' => 5,
        ]);

        $pricing = $this->offers->calculateLinePricing($product, null, 7);

        $this->assertSame(5, $pricing['discounted_quantity']);
        $this->assertSame(2, $pricing['full_price_quantity']);
        // 5 @ 5.00 + 2 @ 10.00
        $this->assertSame(45.0, $pricing['line_subtotal']);
    }

    public function test_ten_units_cap_offer_discount_at_max_discounted_quantity(): void
    {
        $product = $this->createProduct(['price' => 1.75, 'discount' => 0]);
        $this->createOffer($product, [
            'type' => 'percentage',
            'value' => 15,
            'max_discounted_quantity' => 5,
        ]);

        $pricing = $this->offers->calculateLinePricing($product, null, 10);

        $this->assertSame(5, $pricing['discounted_quantity']);
        $this->assertSame(5, $pricing['full_price_quantity']);
        // 5 @ 1.49 + 5 @ 1.75
        $this->assertSame(16.2, $pricing['line_subtotal']);
    }

    public function test_category_scoped_discount_offer_applies_to_product(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct(['price' => 20]);
        $product->categories()->attach($category->id);

        $this->createOffer($category, [
            'type' => 'percentage',
            'value' => 25,
        ]);

        $pricing = $this->offers->calculateLinePricing($product->fresh('categories'), null, 1);

        $this->assertSame(15.0, $pricing['line_subtotal']);
    }

    public function test_resolve_threshold_gift_offer_returns_highest_qualifying_tier_only(): void
    {
        $giftA = $this->createProduct(['price' => 1]);
        $giftB = $this->createProduct(['price' => 2]);

        $lowTier = $this->createThresholdGiftOffer(20, [$giftA->id]);
        $highTier = $this->createThresholdGiftOffer(50, [$giftB->id]);

        $atTwentyFive = $this->offers->resolveThresholdGiftOffer(25);
        $this->assertNotNull($atTwentyFive);
        $this->assertSame($lowTier->id, $atTwentyFive->id);

        $atFiftyFive = $this->offers->resolveThresholdGiftOffer(55);
        $this->assertNotNull($atFiftyFive);
        $this->assertSame($highTier->id, $atFiftyFive->id);
    }

    public function test_resolve_threshold_gift_offer_returns_null_when_cart_is_below_minimum(): void
    {
        $gift = $this->createProduct();
        $this->createThresholdGiftOffer(50, [$gift->id]);

        $this->assertNull($this->offers->resolveThresholdGiftOffer(49.99));
    }

    public function test_threshold_gift_ignores_tier_when_gift_products_are_out_of_stock(): void
    {
        $inStockGift = $this->createProduct(['stock' => 5]);
        $outOfStockGift = $this->createProduct(['stock' => 0]);

        $this->createThresholdGiftOffer(50, [$outOfStockGift->id]);
        $qualifyingOffer = $this->createThresholdGiftOffer(20, [$inStockGift->id]);

        $resolved = $this->offers->resolveThresholdGiftOffer(55);

        $this->assertNotNull($resolved);
        $this->assertSame($qualifyingOffer->id, $resolved->id);
    }

    public function test_resolve_free_delivery_threshold_uses_lowest_active_offer(): void
    {
        $this->createOffer(null, [
            'type' => 'free_delivery',
            'value' => 0,
            'min_cart_amount' => 60,
        ]);
        $this->createOffer(null, [
            'type' => 'free_delivery',
            'value' => 0,
            'min_cart_amount' => 40,
        ]);

        $this->assertSame(40.0, $this->offers->resolveFreeDeliveryThreshold());
    }

    public function test_resolve_free_delivery_threshold_defaults_to_fifty_without_offer(): void
    {
        $this->assertSame(50.0, $this->offers->resolveFreeDeliveryThreshold());
    }

    public function test_qualifies_for_free_delivery_when_subtotal_meets_threshold(): void
    {
        $this->createOffer(null, [
            'type' => 'free_delivery',
            'value' => 0,
            'min_cart_amount' => 30,
        ]);

        $this->assertTrue($this->offers->qualifiesForFreeDelivery(30));
        $this->assertFalse($this->offers->qualifiesForFreeDelivery(29.99));
    }

    public function test_offer_with_null_dates_remains_applicable(): void
    {
        $product = $this->createProduct();
        $offer = $this->createOffer($product, [
            'type' => 'bogo',
            'value' => 1,
            'start_date' => null,
            'end_date' => null,
        ]);

        $this->assertTrue($this->offers->isOfferCurrentlyApplicable($offer));
    }

    public function test_scheduled_offer_is_not_applicable_before_start_date(): void
    {
        $product = $this->createProduct();
        $offer = $this->createOffer($product, [
            'type' => 'bogo',
            'value' => 1,
            'start_date' => Carbon::parse('2026-06-20'),
            'end_date' => null,
        ]);

        $this->assertFalse($this->offers->isOfferCurrentlyApplicable($offer));
    }

    public function test_offer_ends_when_linked_product_is_out_of_stock(): void
    {
        $product = $this->createProduct(['stock' => 0]);
        $offer = $this->createOffer($product, [
            'type' => 'bogo',
            'value' => 1,
            'ends_when_out_of_stock' => true,
        ]);

        $this->assertFalse($this->offers->isOfferCurrentlyApplicable($offer));
    }

    public function test_validate_gift_selection_requires_gift_product_when_cart_qualifies(): void
    {
        $gift = $this->createProduct(['price' => 0]);
        $offer = $this->createThresholdGiftOffer(10, [$gift->id]);

        $this->expectException(ValidationException::class);

        try {
            $this->offers->validateGiftSelection($offer->id, null, 15);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('gift_product_id', $exception->errors());
            throw $exception;
        }
    }

    public function test_validate_gift_selection_rejects_product_not_in_offer(): void
    {
        $gift = $this->createProduct();
        $otherProduct = $this->createProduct();
        $offer = $this->createThresholdGiftOffer(10, [$gift->id]);

        $this->expectException(ValidationException::class);

        try {
            $this->offers->validateGiftSelection($offer->id, $otherProduct->id, 15);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('gift_product_id', $exception->errors());
            throw $exception;
        }
    }

    public function test_build_checkout_preview_exposes_gift_offer_and_free_delivery_meta(): void
    {
        $gift = $this->createProduct(['price' => 1]);
        $giftOffer = $this->createThresholdGiftOffer(25, [$gift->id]);

        $this->createOffer(null, [
            'type' => 'free_delivery',
            'value' => 0,
            'min_cart_amount' => 35,
        ]);

        $preview = $this->offers->buildCheckoutPreview(1, 30);

        $this->assertSame(30.0, $preview['cart_subtotal']);
        $this->assertSame(35.0, $preview['free_delivery_threshold']);
        $this->assertFalse($preview['qualifies_for_free_delivery']);
        $this->assertNotNull($preview['gift_offer']);
        $this->assertSame($giftOffer->id, $preview['gift_offer']['id']);
        $this->assertTrue($preview['gift_offer']['requires_selection']);
        $this->assertCount(1, $preview['gift_offer']['reward_products']);
    }
}

