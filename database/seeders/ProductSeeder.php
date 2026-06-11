<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'sku' => 'APPLE-1KG',
                'slug' => 'red-apples-1kg',
                'name' => ['en' => 'Red Apples 1kg', 'ar' => 'تفاح أحمر 1 كجم'],
                'description' => ['en' => 'Fresh red apples.', 'ar' => 'تفاح أحمر طازج.'],
                'brand' => 'Fresh Farms',
                'category_slug' => 'fruits-vegetables',
                'unit_code' => 'kg',
                'price' => 150,
                'stock' => 120,
                'is_featured' => true,
                'is_new' => true,
            ],
            [
                'sku' => 'MILK-1L',
                'slug' => 'fresh-milk-1l',
                'name' => ['en' => 'Fresh Milk 1L', 'ar' => 'لبن طازج 1 لتر'],
                'description' => ['en' => 'Full cream fresh milk.', 'ar' => 'لبن كامل الدسم.'],
                'brand' => 'Gahez',
                'category_slug' => 'dairy',
                'unit_code' => 'bottle',
                'price' => 60,
                'stock' => 200,
                'is_featured' => true,
            ],
            [
                'sku' => 'BREAD-500G',
                'slug' => 'white-bread-500g',
                'name' => ['en' => 'White Bread 500g', 'ar' => 'خبز أبيض 500 جرام'],
                'description' => ['en' => 'Soft bakery bread.', 'ar' => 'خبز طري من المخبز.'],
                'brand' => 'Gahez',
                'category_slug' => 'bakery',
                'unit_code' => 'piece',
                'price' => 15,
                'stock' => 80,
            ],
            [
                'sku' => 'JUICE-1L',
                'slug' => 'orange-juice-1l',
                'name' => ['en' => 'Orange Juice 1L', 'ar' => 'عصير برتقال 1 لتر'],
                'description' => ['en' => 'No added sugar orange juice.', 'ar' => 'عصير برتقال بدون سكر مضاف.'],
                'brand' => 'Daily Essentials',
                'category_slug' => 'beverages',
                'unit_code' => 'bottle',
                'price' => 70,
                'stock' => 60,
                'is_new' => true,
            ],
            [
                'sku' => 'CHIPS-200G',
                'slug' => 'potato-chips-200g',
                'name' => ['en' => 'Potato Chips 200g', 'ar' => 'شيبسي 200 جرام'],
                'description' => ['en' => 'Classic salted chips.', 'ar' => 'شيبسي مملح كلاسيكي.'],
                'brand' => 'Daily Essentials',
                'category_slug' => 'groceries',
                'unit_code' => 'box',
                'price' => 35,
                'stock' => 150,
            ],
            [
                'sku' => 'WATER-1.5L',
                'slug' => 'mineral-water-1-5l',
                'name' => ['en' => 'Mineral Water 1.5L', 'ar' => 'مياه معدنية 1.5 لتر'],
                'description' => ['en' => 'Still mineral water.', 'ar' => 'مياه معدنية غازية.'],
                'brand' => 'Gahez',
                'category_slug' => 'beverages',
                'unit_code' => 'bottle',
                'price' => 20,
                'stock' => 300,
            ],
            [
                'sku' => 'DETERGENT-1L',
                'slug' => 'laundry-detergent-1l',
                'name' => ['en' => 'Laundry Detergent 1L', 'ar' => 'منظف ملابس 1 لتر'],
                'description' => ['en' => 'Concentrated laundry detergent.', 'ar' => 'منظف ملابس مركز.'],
                'brand' => 'Daily Essentials',
                'category_slug' => 'household',
                'unit_code' => 'bottle',
                'price' => 120,
                'stock' => 45,
            ],
            [
                'sku' => 'TOMATO-1KG',
                'slug' => 'tomatoes-1kg',
                'name' => ['en' => 'Tomatoes 1kg', 'ar' => 'طماطم 1 كجم'],
                'description' => ['en' => 'Ripe red tomatoes.', 'ar' => 'طماطم حمراء طازجة.'],
                'brand' => 'Fresh Farms',
                'category_slug' => 'fruits-vegetables',
                'unit_code' => 'kg',
                'price' => 45,
                'stock' => 90,
            ],
        ];

        foreach ($products as $data) {
            $this->seedProduct($data);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function seedProduct(array $data): Product
    {
        $brand = Brand::query()
            ->where('name->en', $data['brand'])
            ->first();

        if (! $brand) {
            $brand = Brand::query()->first();
        }

        $category = Category::query()
            ->where('slug', $data['category_slug'])
            ->first();

        $unit = Unit::query()
            ->where('code', $data['unit_code'])
            ->first();

        if (! $unit) {
            $unit = Unit::query()->where('code', 'piece')->first();
        }

        $product = Product::query()->updateOrCreate(
            ['sku' => $data['sku']],
            [
                'type' => 'simple',
                'name' => $data['name'],
                'description' => $data['description'],
                'slug' => $data['slug'],
                'discount' => 0,
                'discount_type' => null,
                'is_active' => true,
                'is_featured' => $data['is_featured'] ?? false,
                'is_new' => $data['is_new'] ?? false,
                'is_approved' => true,
                'is_bookable' => true,
                'is_in_stock' => true,
                'brand_id' => $brand?->id,
            ],
        );

        if ($category) {
            $product->categories()->syncWithoutDetaching([$category->id]);
        }

        if ($unit) {
            ProductUnit::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'unit_id' => $unit->id,
                ],
                [
                    'sku' => $data['sku'],
                    'price' => $data['price'],
                    'stock' => $data['stock'],
                    'is_in_stock' => true,
                    'factor' => 1,
                    'discount' => null,
                    'discount_type' => null,
                    'is_default' => true,
                    'sort_order' => 0,
                    'is_active' => true,
                ],
            );
        }

        return $product;
    }
}
