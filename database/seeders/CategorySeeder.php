<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $groceries = Category::query()->updateOrCreate(
            ['slug' => 'groceries'],
            [
                'name' => ['en' => 'Groceries', 'ar' => 'بقالة'],
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
        );

        $categories = [
            ['slug' => 'fruits-vegetables', 'name' => ['en' => 'Fruits & Vegetables', 'ar' => 'فواكه وخضروات'], 'parent_id' => $groceries->id, 'sort_order' => 1],
            ['slug' => 'dairy', 'name' => ['en' => 'Dairy', 'ar' => 'ألبان'], 'parent_id' => $groceries->id, 'sort_order' => 2],
            ['slug' => 'beverages', 'name' => ['en' => 'Beverages', 'ar' => 'مشروبات'], 'parent_id' => $groceries->id, 'sort_order' => 3],
            ['slug' => 'bakery', 'name' => ['en' => 'Bakery', 'ar' => 'مخبوزات'], 'parent_id' => null, 'sort_order' => 2, 'is_featured' => true],
            ['slug' => 'household', 'name' => ['en' => 'Household', 'ar' => 'مستلزمات منزلية'], 'parent_id' => null, 'sort_order' => 3],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'parent_id' => $category['parent_id'],
                    'is_active' => true,
                    'is_featured' => $category['is_featured'] ?? false,
                    'sort_order' => $category['sort_order'],
                ],
            );
        }
    }
}
