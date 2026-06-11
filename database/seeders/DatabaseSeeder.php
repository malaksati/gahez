<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SettingSeeder::class,
            BranchSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            VariantSeeder::class,
            UnitSeeder::class,
            ProductSeeder::class,
            CouponSeeder::class,
            OfferSeeder::class,
            GoalSeeder::class,
            UserSeeder::class,
            AddressSeeder::class,
            OrderSeeder::class,
            CustomerEngagementSeeder::class,
        ]);
    }
}
