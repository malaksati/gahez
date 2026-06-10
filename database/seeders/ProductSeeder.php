<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use App\Models\Setting;
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
                'sku' => 'WATER-6PK',
                'slug' => 'mineral-water-6-pack',
                'name' => ['en' => 'Mineral Water 6-Pack', 'ar' => 'مياه معدنية ٦ عبوات'],
                'description' => ['en' => 'Still mineral water.', 'ar' => 'مياه معدنية غير غازية.'],
                'price' => 1.20,
                'stock' => 150,
                'discount' => 10,
                'discount_type' => 'percentage',
                'categories' => [],
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
            unset($data['categories']);

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
                'price' => 4.50,
                'stock' => 0,
                'brand_id' => $brand?->id,
                'is_active' => true,
                'is_approved' => true,
                'is_bookable' => true,
                'is_in_stock' => true,
                'sort_order' => 10,
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

        $product->update([
            'stock' => (int) ProductVariant::query()->where('product_id', $product->id)->sum('stock'),
        ]);
    }
}
