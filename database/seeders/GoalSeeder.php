<?php

namespace Database\Seeders;

use App\Models\Goal;
use Illuminate\Database\Seeder;

class GoalSeeder extends Seeder
{
    public function run(): void
    {
        $goals = [
            [
                'name' => [
                    'en' => 'Daily Goal',
                    'ar' => 'الهدف اليومي',
                ],
                'description' => [
                    'en' => '50 EGP cashback when your orders reach 500 EGP today',
                    'ar' => 'كاش باك 50 ج.م لو فاتورتك ب 500 ج.م اليوم',
                ],
                'period_type' => Goal::PERIOD_DAILY,
                'min_order_total' => 500,
                'reward_amount' => 50,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => [
                    'en' => 'Weekly Goal',
                    'ar' => 'الهدف الأسبوعي',
                ],
                'description' => [
                    'en' => '1000 EGP cashback when your orders reach 2000 EGP this week',
                    'ar' => 'كاش باك 1000 ج.م لو فاتورتك ب 2000 ج.م',
                ],
                'period_type' => Goal::PERIOD_WEEKLY,
                'min_order_total' => 2000,
                'reward_amount' => 1000,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => [
                    'en' => 'Monthly Goal',
                    'ar' => 'الهدف الشهري',
                ],
                'description' => [
                    'en' => '2500 EGP cashback when your orders reach 10000 EGP this month',
                    'ar' => 'كاش باك 2500 ج.م لو فاتورتك ب 10000 ج.م هذا الشهر',
                ],
                'period_type' => Goal::PERIOD_MONTHLY,
                'min_order_total' => 10000,
                'reward_amount' => 2500,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($goals as $data) {
            Goal::query()->updateOrCreate(
                [
                    'period_type' => $data['period_type'],
                    'min_order_total' => $data['min_order_total'],
                ],
                $data,
            );
        }
    }
}
