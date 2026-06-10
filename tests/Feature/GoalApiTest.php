<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Models\WalletTransaction;
use App\V1\Services\GoalService;
use App\V1\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesOfferFixtures;
use Tests\TestCase;

class GoalApiTest extends TestCase
{
    use CreatesOfferFixtures;

    use RefreshDatabase;

    public function test_user_can_list_active_goals_with_progress(): void
    {
        $user = $this->createUser();

        Goal::query()->create([
            'name' => ['en' => 'Weekly Goal', 'ar' => 'الهدف الأسبوعي'],
            'description' => null,
            'period_type' => Goal::PERIOD_WEEKLY,
            'min_order_total' => 2000,
            'reward_amount' => 1000,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/goals');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.period_type', 'weekly')
            ->assertJsonPath('data.0.min_order_total', 2000)
            ->assertJsonPath('data.0.reward_amount', 1000)
            ->assertJsonPath('data.0.progress_percent', 0)
            ->assertJsonPath('data.0.is_achieved', false);
    }

    public function test_delivered_orders_trigger_goal_wallet_reward_once_per_period(): void
    {
        setting_forget();
        Setting::query()->updateOrCreate(
            ['key' => 'cashback_percentage'],
            ['value' => '0', 'type' => 'number'],
        );

        $user = $this->createUser();

        $goal = Goal::query()->create([
            'name' => ['en' => 'Weekly Goal', 'ar' => 'هدف'],
            'period_type' => Goal::PERIOD_WEEKLY,
            'min_order_total' => 100,
            'reward_amount' => 50,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $branchId = \App\Models\Branch::query()->create([
            'name' => ['en' => 'Main', 'ar' => 'رئيسي'],
            'address' => 'Addr',
            'is_active' => true,
        ])->id;

        $order = Order::query()->create([
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'sub_total' => 120,
            'total' => 120,
            'status' => 'shipped',
            'payment_status' => 'paid',
            'payment_method' => 'cash_on_delivery',
            'refund_status' => 'none',
        ]);

        app(OrderService::class)->updateOrderStatus($order, 'delivered');

        $user->refresh();
        $this->assertSame(50.0, (float) $user->wallet);
        $this->assertSame(1, WalletTransaction::query()->count());

        $payload = app(GoalService::class)->buildProgressPayload($goal, $user);
        $this->assertTrue($payload['is_achieved']);
        $this->assertEquals(100, $payload['progress_percent']);

        $secondOrder = Order::query()->create([
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'sub_total' => 50,
            'total' => 50,
            'status' => 'shipped',
            'payment_status' => 'paid',
            'payment_method' => 'cash_on_delivery',
            'refund_status' => 'none',
        ]);

        app(OrderService::class)->updateOrderStatus($secondOrder, 'delivered');

        $user->refresh();
        $this->assertSame(50.0, (float) $user->wallet);
        $this->assertSame(1, WalletTransaction::query()->count());
    }

    public function test_admin_can_toggle_goal_active(): void
    {
        Permission::findOrCreate('manage goals', 'web');
        $role = Role::findOrCreate('super-admin', 'web');
        $role->givePermissionTo('manage goals');

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('super-admin');

        $goal = Goal::query()->create([
            'name' => ['en' => 'Daily', 'ar' => 'يومي'],
            'period_type' => Goal::PERIOD_DAILY,
            'min_order_total' => 10,
            'reward_amount' => 5,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->actingAs($admin)
            ->withHeader('Accept', 'application/json')
            ->post(route('v1.admin.goals.toggle-active', $goal), ['_token' => csrf_token()])
            ->assertOk();

        $this->assertFalse($goal->fresh()->is_active);
    }
}
