<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['en' => 'Gahez', 'ar' => 'جاهز'],
            ['en' => 'Fresh Farms', 'ar' => 'المزارع الطازجة'],
            ['en' => 'Daily Essentials', 'ar' => 'الأساسيات اليومية'],
        ];

        foreach ($brands as $name) {
            Brand::query()->firstOrCreate(
                ['name->en' => $name['en']],
                ['name' => $name],
            );
        }
    }
}
