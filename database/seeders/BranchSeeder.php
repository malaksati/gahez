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
                'address' => 'Salmiya, Kuwait',
                'latitude' => '29.3375',
                'longitude' => '48.0758',
                'phone' => '22223333',
                'is_active' => true,
            ],
        );
    }
}
