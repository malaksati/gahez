<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesOfferFixtures;
use Tests\TestCase;

class CartCheckoutApiTest extends TestCase
{
    use CreatesOfferFixtures;

    use RefreshDatabase;

    public function test_customer_can_add_product_to_cart_via_api(): void
    {
        [$user, $product] = $this->createCustomerWithProduct();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/cart/{$product->id}", [
            'quantity' => 2,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.quantity', 2)
            ->assertJsonPath('data.product.id', $product->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_simple_product_ignores_foreign_variant_id_via_api(): void
    {
        [$user, $product] = $this->createCustomerWithProduct();
        $otherProduct = Product::query()->create([
            'type' => 'variable',
            'sku' => 'VAR-API-TEST',
            'slug' => 'var-api-test',
            'name' => ['en' => 'Variable product', 'ar' => 'منتج متغير'],
            'description' => ['en' => 'Test', 'ar' => 'اختبار'],
            'price' => 4.75,
            'stock' => 0,
            'discount' => 0,
            'discount_type' => null,
            'is_active' => true,
            'is_approved' => true,
            'is_bookable' => true,
            'brand_id' => $product->brand_id,
        ]);
        $variant = \App\Models\ProductVariant::query()->create([
            'product_id' => $otherProduct->id,
            'sku' => 'VAR-API-TEST-1',
            'slug' => 'var-api-test-1',
            'name' => ['en' => 'Variant', 'ar' => 'متغير'],
            'price' => 4.75,
            'stock' => 10,
            'is_active' => true,
            'is_in_stock' => true,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/cart/{$product->id}", [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertSuccessful()
            ->assertJsonPath('data.variant', null);
    }

    public function test_offer_discount_caps_at_max_discounted_quantity_in_cart_api(): void
    {
        [$user, $product] = $this->createCustomerWithProduct(price: 1.75);
        $this->createOffer($product, [
            'type' => 'percentage',
            'value' => 15,
            'max_discounted_quantity' => 5,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/cart/{$product->id}", ['quantity' => 10])->assertSuccessful();

        $this->getJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonPath('data.0.quantity', 10)
            ->assertJsonPath('data.0.discounted_quantity', 5)
            ->assertJsonPath('data.0.full_price_quantity', 5)
            ->assertJsonPath('meta.subtotal', 16.2);
    }

    public function test_customer_can_view_cart_with_subtotal(): void
    {
        [$user, $product] = $this->createCustomerWithProduct(price: 15);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/cart/{$product->id}", ['quantity' => 2])->assertSuccessful();

        $response = $this->getJson('/api/v1/cart');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total_quantity', 2)
            ->assertJsonPath('meta.subtotal', 30);
    }

    public function test_customer_can_checkout_cart_and_create_order(): void
    {
        [$user, $product, $address] = $this->createCheckoutFixtures(productPrice: 20, quantity: 2);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/cart/{$product->id}", ['quantity' => 2])->assertSuccessful();

        $response = $this->postJson('/api/v1/orders', [
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'notes' => 'Leave at door',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.payment_status', 'pending')
            ->assertJsonPath('data.payment_method', 'cash_on_delivery')
            ->assertJsonPath('data.sub_total', '40.00')
            ->assertJsonPath('data.total_shipping', '5.00')
            ->assertJsonPath('data.total', '45.00');

        $orderId = $response->json('data.id');

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'user_id' => $user->id,
            'address_id' => $address->id,
            'sub_total' => 40,
            'total_shipping' => 5,
            'total' => 45,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $orderId,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_saves_per_item_notes(): void
    {
        [$user, $product, $address] = $this->createCheckoutFixtures(productPrice: 10, quantity: 1);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/cart/{$product->id}", ['quantity' => 1])->assertSuccessful();

        $response = $this->postJson('/api/v1/orders', [
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'item_notes' => [
                [
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'note' => 'No onions please',
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.items.0.note', 'No onions please');

        $this->assertDatabaseHas('order_items', [
            'order_id' => $response->json('data.id'),
            'product_id' => $product->id,
            'note' => 'No onions please',
        ]);
    }

    public function test_checkout_fails_when_cart_is_empty(): void
    {
        $user = $this->createUser();
        $address = $this->createAddressForUser($user);
        $this->setDefaultShippingFee(5);
        $this->createMainBranch();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/orders', [
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['cart']);
    }

    /**
     * @return array{0: User, 1: Product}
     */
    protected function createCustomerWithProduct(float $price = 10): array
    {
        $user = $this->createUser();
        $product = $this->createProduct(['price' => $price, 'stock' => 100]);

        return [$user, $product];
    }

    /**
     * @return array{0: User, 1: Product, 2: Address}
     */
    protected function createCheckoutFixtures(float $productPrice = 20, int $quantity = 1): array
    {
        $user = $this->createUser();
        $product = $this->createProduct(['price' => $productPrice, 'stock' => 100]);
        $this->setDefaultShippingFee(5);
        $this->createMainBranch();
        $address = $this->createAddressForUser($user);

        return [$user, $product, $address];
    }

    protected function setDefaultShippingFee(float $fee): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'shipping_price_per_km'],
            ['value' => (string) $fee, 'type' => 'number'],
        );
        setting_forget('shipping_price_per_km');
    }

    protected function createAddressForUser(User $user): Address
    {
        return Address::query()->create([
            'user_id' => $user->id,
            'address' => 'Customer address',
            'latitude' => '29.3',
            'longitude' => '47.9',
            'name' => 'Customer',
            'phone' => '50000000',
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    protected function createMainBranch(): Branch
    {
        return Branch::query()->create([
            'name' => ['en' => 'Main Branch', 'ar' => 'الفرع الرئيسي'],
            'address' => 'Branch address',
            'is_active' => true,
        ]);
    }
}
