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
                'address' => 'Block 5, Street 12, Salmiya',
                'latitude' => '29.3375',
                'longitude' => '48.0758',
                'phone' => '50001111',
                'city' => 'Salmiya',
                'state' => 'Hawalli',
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
                'address' => 'Block 2, Street 4, Hawally',
                'latitude' => '29.3330',
                'longitude' => '48.0280',
                'phone' => '50001111',
                'city' => 'Hawally',
                'state' => 'Hawalli',
                'is_default' => false,
                'is_active' => true,
            ],
        );
    }
}
