<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::query()->where('email', 'customer1@gmail.com')->first();

        if (! $customer) {
            return;
        }

        Address::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'name' => 'Home',
            ],
            [
                'address' => 'Nasr City, Cairo, Egypt',
                'latitude' => '30.0561',
                'longitude' => '31.3300',
                'phone' => '+201000111111',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'is_default' => true,
                'is_active' => true,
            ],
        );

        Address::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'name' => 'Office',
            ],
            [
                'address' => 'Zamalek, Cairo, Egypt',
                'latitude' => '30.0626',
                'longitude' => '31.2197',
                'phone' => '+201000111111',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'is_default' => false,
                'is_active' => true,
            ],
        );
    }
}
