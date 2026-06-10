<?php

namespace Database\Seeders;

use App\Models\Variant;
use App\Models\VariantOption;
use Illuminate\Database\Seeder;

class VariantSeeder extends Seeder
{
    public function run(): void
    {
        $size = Variant::query()->updateOrCreate(
            ['name->en' => 'Size'],
            [
                'name' => ['en' => 'Size', 'ar' => 'المقاس'],
                'is_required' => true,
                'is_active' => true,
            ],
        );

        foreach ([
            ['code' => 'S', 'en' => 'Small', 'ar' => 'صغير'],
            ['code' => 'M', 'en' => 'Medium', 'ar' => 'وسط'],
            ['code' => 'L', 'en' => 'Large', 'ar' => 'كبير'],
        ] as $option) {
            VariantOption::query()->updateOrCreate(
                ['variant_id' => $size->id, 'code' => $option['code']],
                ['name' => ['en' => $option['en'], 'ar' => $option['ar']]],
            );
        }

        $color = Variant::query()->updateOrCreate(
            ['name->en' => 'Color'],
            [
                'name' => ['en' => 'Color', 'ar' => 'اللون'],
                'is_required' => false,
                'is_active' => true,
            ],
        );

        foreach ([
            ['code' => 'BLK', 'en' => 'Black', 'ar' => 'أسود'],
            ['code' => 'WHT', 'en' => 'White', 'ar' => 'أبيض'],
        ] as $option) {
            VariantOption::query()->updateOrCreate(
                ['variant_id' => $color->id, 'code' => $option['code']],
                ['name' => ['en' => $option['en'], 'ar' => $option['ar']]],
            );
        }
    }
}
