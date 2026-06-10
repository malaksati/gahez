<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\ProductUnit;
use App\Models\VariantOption;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::query()->first();
        $fruits = Category::query()->where('slug', 'fruits-vegetables')->first();
        $dairy = Category::query()->where('slug', 'dairy')->first();
        $bakery = Category::query()->where('slug', 'bakery')->first();

        $products = [
            [
                'sku' => 'APPLE-1KG',
                'slug' => 'fresh-apples-1kg',
                'name' => ['en' => 'Fresh Apples 1kg', 'ar' => 'تفاح طازج ١ كجم'],
                'description' => ['en' => 'Crisp red apples.', 'ar' => 'تفاح أحمر مقرمش.'],
                'price' => 1.75,
                'stock' => 120,
                'is_featured' => true,
                'is_new' => true,
                'categories' => [$fruits?->id],
            ],
            [
                'sku' => 'MILK-1L',
                'slug' => 'fresh-milk-1l',
                'name' => ['en' => 'Fresh Milk 1L', 'ar' => 'حليب طازج ١ لتر'],
                'description' => ['en' => 'Full cream fresh milk.', 'ar' => 'حليب كامل الدسم.'],
                'price' => 0.95,
                'stock' => 200,
                'is_featured' => true,
                'categories' => [$dairy?->id],
            ],
            [
                'sku' => 'BREAD-WHITE',
                'slug' => 'white-bread-loaf',
                'name' => ['en' => 'White Bread Loaf', 'ar' => 'خبز أبيض'],
                'description' => ['en' => 'Soft bakery loaf.', 'ar' => 'رغيف مخبوز طري.'],
                'price' => 0.65,
                'stock' => 80,
                'categories' => [$bakery?->id],
            ],
            [
                'sku' => 'WATER',
                'slug' => 'mineral-water',
                'name' => ['en' => 'Mineral Water', 'ar' => 'مياه معدنية'],
                'description' => ['en' => 'Still mineral water in bottle or box.', 'ar' => 'مياه معدنية غير غازية بزجاجة أو صندوق.'],
                'price' => 0.35,
                'stock' => 500,
                'categories' => [],
                'product_units' => [
                    [
                        'unit_code' => 'bottle',
                        'sku' => 'WATER-1B',
                        'price' => 0.35,
                        'stock' => 500,
                        'factor' => 1,
                        'is_default' => true,
                    ],
                    [
                        'unit_code' => 'box',
                        'sku' => 'WATER-12BOX',
                        'price' => 3.60,
                        'stock' => 80,
                        'factor' => 12,
                        'is_default' => false,
                    ],
                ],
            ],
            [
                'sku' => 'GIFT-BOX',
                'slug' => 'birthday-gift-box',
                'name' => ['en' => 'Birthday Gift Box', 'ar' => 'صندوق هدية عيد ميلاد'],
                'description' => ['en' => 'Small gift box for promotions.', 'ar' => 'صندوق هدايا صغير للعروض.'],
                'price' => 3.50,
                'stock' => 50,
                'categories' => [],
            ],
        ];

        foreach ($products as $data) {
            $categoryIds = array_filter($data['categories'] ?? []);
            $unitRows = $data['product_units'] ?? null;
            $defaultUnitPrice = $data['price'] ?? 0;
            $defaultUnitStock = $data['stock'] ?? null;
            unset($data['categories'], $data['product_units'], $data['price'], $data['stock']);

            $product = Product::query()->updateOrCreate(
                ['sku' => $data['sku']],
                array_merge([
                    'type' => 'simple',
                    'brand_id' => $brand?->id,
                    'discount' => 0,
                    'discount_type' => null,
                    'is_active' => true,
                    'is_approved' => true,
                    'is_bookable' => true,
                    'is_in_stock' => true,
                ], $data),
            );

            if ($categoryIds) {
                $product->categories()->syncWithoutDetaching($categoryIds);
            }

            if ($unitRows !== null) {
                $this->syncProductUnits($product, $unitRows);
            } else {
                $this->ensureDefaultProductUnit($product, $defaultUnitPrice, $defaultUnitStock);
            }
        }

        $this->seedVariableTShirt($brand);

        $giftProduct = Product::query()->where('sku', 'GIFT-BOX')->first();
        if ($giftProduct) {
            Setting::query()->updateOrCreate(
                ['key' => 'birthday_gift_product_id'],
                ['value' => (string) $giftProduct->id, 'type' => 'number'],
            );
            setting_forget('birthday_gift_product_id');
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function syncProductUnits(Product $product, array $rows): void
    {
        ProductUnit::query()->where('product_id', $product->id)->delete();

        foreach ($rows as $index => $row) {
            $unitId = Unit::query()->where('code', $row['unit_code'])->value('id');

            if (! $unitId) {
                continue;
            }

            ProductUnit::query()->create([
                'product_id' => $product->id,
                'unit_id' => $unitId,
                'sku' => $row['sku'] ?? null,
                'price' => $row['price'] ?? 0,
                'stock' => $row['stock'] ?? null,
                'is_in_stock' => true,
                'factor' => max(1, (int) ($row['factor'] ?? 1)),
                'is_default' => (bool) ($row['is_default'] ?? $index === 0),
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }

    protected function ensureDefaultProductUnit(Product $product, float $price = 0, ?int $stock = null): void
    {
        if (ProductUnit::query()->where('product_id', $product->id)->exists()) {
            return;
        }

        $pieceUnitId = Unit::query()->where('code', 'piece')->value('id');

        if (! $pieceUnitId) {
            return;
        }

        ProductUnit::query()->create([
            'product_id' => $product->id,
            'unit_id' => $pieceUnitId,
            'price' => $price,
            'stock' => $stock,
            'is_in_stock' => $product->is_in_stock,
            'factor' => 1,
            'is_default' => true,
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }

    protected function seedVariableTShirt(?Brand $brand): void
    {
        $sizeSmall = VariantOption::query()->where('code', 'S')->first();
        $sizeMedium = VariantOption::query()->where('code', 'M')->first();
        $colorBlack = VariantOption::query()->where('code', 'BLK')->first();

        if (! $sizeSmall || ! $sizeMedium || ! $colorBlack) {
            return;
        }

        $product = Product::query()->updateOrCreate(
            ['sku' => 'TSHIRT-VAR'],
            [
                'type' => 'variable',
                'slug' => 'cotton-t-shirt',
                'name' => ['en' => 'Cotton T-Shirt', 'ar' => 'تيشيرت قطني'],
                'description' => ['en' => 'Comfortable cotton tee.', 'ar' => 'تيشيرت قطني مريح.'],
                'brand_id' => $brand?->id,
                'is_active' => true,
                'is_approved' => true,
                'is_bookable' => true,
                'is_in_stock' => true,
            ],
        );

        $combinations = [
            [
                'sku' => 'TSHIRT-S-BLK',
                'slug' => 'cotton-t-shirt-s-black',
                'name' => ['en' => 'Small / Black', 'ar' => 'صغير / أسود'],
                'price' => 4.50,
                'stock' => 25,
                'option_ids' => [$sizeSmall->id, $colorBlack->id],
            ],
            [
                'sku' => 'TSHIRT-M-BLK',
                'slug' => 'cotton-t-shirt-m-black',
                'name' => ['en' => 'Medium / Black', 'ar' => 'وسط / أسود'],
                'price' => 4.75,
                'stock' => 30,
                'option_ids' => [$sizeMedium->id, $colorBlack->id],
            ],
        ];

        foreach ($combinations as $row) {
            $optionIds = $row['option_ids'];
            unset($row['option_ids']);

            $variant = ProductVariant::query()->updateOrCreate(
                ['sku' => $row['sku']],
                array_merge($row, [
                    'product_id' => $product->id,
                    'discount' => 0,
                    'discount_type' => 'percentage',
                    'is_active' => true,
                    'is_in_stock' => true,
                ]),
            );

            $variant->values()->delete();

            foreach ($optionIds as $optionId) {
                $option = VariantOption::query()->find($optionId);
                if (! $option) {
                    continue;
                }

                ProductVariantValue::query()->create([
                    'product_variant_id' => $variant->id,
                    'variant_option_id' => $optionId,
                    'value' => $option->getTranslations('name'),
                ]);
            }
        }

    }
}
