<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['code' => 'piece', 'name' => ['en' => 'piece', 'ar' => 'قطعة']],
            ['code' => 'bottle', 'name' => ['en' => 'bottle', 'ar' => 'زجاجة']],
            ['code' => 'carton', 'name' => ['en' => 'carton', 'ar' => 'كرتونة']],
            ['code' => 'box', 'name' => ['en' => 'box', 'ar' => 'صندوق']],
            ['code' => 'kg', 'name' => ['en' => 'kg', 'ar' => 'كجم']],
        ];

        foreach ($units as $unit) {
            Unit::query()->updateOrCreate(
                ['code' => $unit['code']],
                [
                    'name' => $unit['name'],
                    'is_active' => true,
                ],
            );
        }
    }
}
