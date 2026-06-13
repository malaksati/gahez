<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::query()->updateOrCreate(
            ['id' => 1],
            [
                'name' => ['en' => 'Main Branch', 'ar' => 'الفرع الرئيسي'],
                'address' => 'Nasr City, Cairo, Egypt',
                'latitude' => '30.0444',
                'longitude' => '31.2357',
                'phone' => '+201012345678',
                'is_active' => true,
            ],
        );
    }
}
