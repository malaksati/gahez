<?php

namespace Database\Seeders;

use App\Models\Goal;
use App\Models\GoalAchievement;
use App\Models\Order;
use App\Models\OrderRating;
use App\Models\OrderRefundRequest;
use App\Models\PointTransaction;
use App\Models\Product;
use App\Models\ProductRating;
use App\Models\ProductReport;
use App\Models\Support;
use App\Models\SupportMessage;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Wishlist;
use App\V1\Services\GoalService;
use Database\Seeders\Concerns\BuildsRealisticOrders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CustomerEngagementSeeder extends Seeder
{
    use BuildsRealisticOrders;
    public function run(): void
    {
        $customer = User::query()->where('email', 'customer1@gmail.com')->first();
        $admin = User::query()->where('email', 'admin@gmail.com')->first();
        $address = $customer
            ? $customer->addresses()->where('is_default', true)->first()
            : null;

        if (! $customer || ! $admin || ! $address) {
            return;
        }

        $products = Product::query()->orderBy('id')->get();

        if ($products->isEmpty()) {
            return;
        }

        $this->seedSupportChats($customer, $admin);
        $this->seedTickets($customer, $admin);
        $this->seedGoalAchievements($customer);
        $this->seedPointHistory($customer);
        $this->seedRefundedOrder($customer, $admin, $address);
        $this->seedProductRatings($customer, $products);
        $this->seedProductReports($customer, $admin, $products);
        $this->seedWishlists($customer, $products);
        $this->seedOrderRating($customer);
    }

    protected function seedSupportChats(User $customer, User $admin): void
    {
        $openChat = Support::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'subject' => 'Late delivery question',
            ],
            [
                'assigned_admin_id' => $admin->id,
                'status' => 'open',
                'last_message_at' => now()->subMinutes(10),
                'closed_at' => null,
            ],
        );

        $this->seedSupportMessage($openChat->id, $customer->id, 'user', 'Hi, my order is delayed. Can you check?', now()->subHours(2));
        $this->seedSupportMessage($openChat->id, $admin->id, 'admin', 'We are checking with the branch now.', now()->subHour());
        $this->seedSupportMessage($openChat->id, $customer->id, 'user', 'Thanks, waiting for an update.', now()->subMinutes(10));

        $closedChat = Support::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'subject' => 'Payment issue',
            ],
            [
                'assigned_admin_id' => $admin->id,
                'status' => 'closed',
                'last_message_at' => now()->subDays(3),
                'closed_at' => now()->subDays(3),
            ],
        );

        $this->seedSupportMessage($closedChat->id, $customer->id, 'user', 'I was charged twice for one order.', now()->subDays(4));
        $this->seedSupportMessage($closedChat->id, $admin->id, 'admin', 'Refund has been processed. Chat closed.', now()->subDays(3));
    }

    protected function seedSupportMessage(int $supportId, int $senderId, string $senderType, string $message, Carbon $at): void
    {
        SupportMessage::query()->updateOrCreate(
            [
                'support_id' => $supportId,
                'sender_id' => $senderId,
                'sender_type' => $senderType,
                'message' => $message,
            ],
            [
                'attachments' => null,
                'read_at' => $senderType === 'admin' ? $at : null,
                'created_at' => $at,
                'updated_at' => $at,
            ],
        );
    }

    protected function seedTickets(User $customer, User $admin): void
    {
        $pendingTicket = Ticket::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'subject' => 'Missing item in order',
            ],
            [
                'description' => 'One milk bottle was missing from my last delivery.',
                'status' => 'pending',
                'attachments' => null,
            ],
        );

        TicketMessage::query()->updateOrCreate(
            [
                'ticket_id' => $pendingTicket->id,
                'sender_id' => $customer->id,
                'message' => 'Please send the missing milk or refund the item.',
            ],
            [
                'sender_type' => 'user',
                'attachments' => null,
            ],
        );

        $resolvedTicket = Ticket::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'subject' => 'App login issue',
            ],
            [
                'description' => 'Could not log in with phone number.',
                'status' => 'resolved',
                'attachments' => null,
            ],
        );

        TicketMessage::query()->updateOrCreate(
            [
                'ticket_id' => $resolvedTicket->id,
                'sender_id' => $customer->id,
                'message' => 'Login fails after password reset.',
            ],
            [
                'sender_type' => 'user',
                'attachments' => null,
            ],
        );

        TicketMessage::query()->updateOrCreate(
            [
                'ticket_id' => $resolvedTicket->id,
                'sender_id' => $admin->id,
                'message' => 'Please try again after clearing app cache. Issue resolved.',
            ],
            [
                'sender_type' => 'admin',
                'attachments' => null,
            ],
        );
    }

    protected function seedGoalAchievements(User $customer): void
    {
        $dailyGoal = Goal::query()->where('period_type', Goal::PERIOD_DAILY)->first();
        $weeklyGoal = Goal::query()->where('period_type', Goal::PERIOD_WEEKLY)->first();

        if (! $dailyGoal && ! $weeklyGoal) {
            return;
        }

        $goalService = app(GoalService::class);
        $wallet = (float) $customer->wallet;

        if ($dailyGoal) {
            $bounds = $goalService->currentPeriodBounds(Goal::PERIOD_DAILY, now()->subWeek());
            $reward = (float) $dailyGoal->reward_amount;
            $wallet += $reward;

            GoalAchievement::query()->updateOrCreate(
                [
                    'goal_id' => $dailyGoal->id,
                    'user_id' => $customer->id,
                    'period_start' => $bounds['start']->toDateString(),
                ],
                [
                    'period_end' => $bounds['end']->toDateString(),
                    'order_total' => (float) $dailyGoal->min_order_total,
                    'reward_amount' => $reward,
                    'awarded_at' => $bounds['end']->copy()->subHours(2),
                ],
            );

            WalletTransaction::query()->updateOrCreate(
                [
                    'user_id' => $customer->id,
                    'notes' => 'Seeded daily goal reward',
                ],
                [
                    'type' => 'addition',
                    'amount' => $reward,
                    'balance_after' => $wallet,
                ],
            );
        }

        if ($weeklyGoal) {
            $bounds = $goalService->currentPeriodBounds(Goal::PERIOD_WEEKLY, now()->subWeeks(2));
            $reward = (float) $weeklyGoal->reward_amount;
            $wallet += $reward;

            GoalAchievement::query()->updateOrCreate(
                [
                    'goal_id' => $weeklyGoal->id,
                    'user_id' => $customer->id,
                    'period_start' => $bounds['start']->toDateString(),
                ],
                [
                    'period_end' => $bounds['end']->toDateString(),
                    'order_total' => (float) $weeklyGoal->min_order_total + 150,
                    'reward_amount' => $reward,
                    'awarded_at' => $bounds['end']->copy()->subDay(),
                ],
            );

            WalletTransaction::query()->updateOrCreate(
                [
                    'user_id' => $customer->id,
                    'notes' => 'Seeded weekly goal reward',
                ],
                [
                    'type' => 'addition',
                    'amount' => $reward,
                    'balance_after' => $wallet,
                ],
            );
        }

        $customer->update(['wallet' => $wallet]);
    }

    protected function seedPointHistory(User $customer): void
    {
        PointTransaction::query()->where('user_id', $customer->id)->delete();

        $transactions = [
            [
                'type' => 'addition',
                'amount' => 80,
                'balance_after' => 80,
                'notes' => 'Cashback for delivered order',
                'created_at' => now()->subDays(5),
            ],
            [
                'type' => 'addition',
                'amount' => 50,
                'balance_after' => 130,
                'notes' => 'Welcome bonus points',
                'created_at' => now()->subDays(10),
            ],
            [
                'type' => 'subtraction',
                'amount' => 20,
                'balance_after' => 110,
                'notes' => 'Points used on order discount',
                'created_at' => now()->subDays(3),
            ],
            [
                'type' => 'addition',
                'amount' => 40,
                'balance_after' => 150,
                'notes' => 'Cashback for second delivered order',
                'created_at' => now()->subDay(),
            ],
        ];

        foreach ($transactions as $row) {
            PointTransaction::query()->create([
                'user_id' => $customer->id,
                'type' => $row['type'],
                'amount' => $row['amount'],
                'balance_after' => $row['balance_after'],
                'notes' => $row['notes'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['created_at'],
            ]);
        }

        $customer->update(['points' => 150]);
    }

    protected function seedRefundedOrder(User $customer, User $admin, $address): void
    {
        $order = $this->seedOrderForCustomer(
            $customer,
            $address,
            'Demo refunded order',
            [
                'APPLE-1KG' => 6,
                'MILK-1L' => 8,
                'WATER-1.5L' => 20,
                'DETERGENT-1L' => 5,
                'TOMATO-1KG' => 8,
            ],
            [
                'status' => 'refunded',
                'payment_status' => 'refunded',
                'paid_at' => now()->subDays(7),
                'refund_status' => 'refunded',
                'refunded_total' => 0,
            ],
        );

        $order->update(['refunded_total' => $order->total]);

        OrderRefundRequest::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'user_id' => $customer->id,
                'status' => 'approved',
                'reason' => 'damaged_items',
                'details' => 'Products arrived with damaged packaging.',
                'processed_by' => $admin->id,
                'processed_at' => now()->subDays(6),
            ],
        );

        $deliveredOrderId = Order::query()
            ->where('user_id', $customer->id)
            ->where('notes', 'Demo delivered order')
            ->value('id');

        if ($deliveredOrderId) {
            OrderRefundRequest::query()->updateOrCreate(
                ['order_id' => $deliveredOrderId],
                [
                    'user_id' => $customer->id,
                    'status' => 'pending',
                    'reason' => 'wrong_item',
                    'details' => 'Received apples instead of milk.',
                    'processed_by' => null,
                    'processed_at' => null,
                ],
            );
        }
    }

    protected function seedProductRatings(User $customer, $products): void
    {
        $comments = [
            'Great quality and fast delivery.',
            'Good value for money.',
            'Fresh product, will order again.',
            'Average taste, packaging was fine.',
            'Excellent! Highly recommended.',
        ];

        foreach ($products->take(5) as $index => $product) {
            ProductRating::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'user_id' => $customer->id,
                ],
                [
                    'rating' => 4 + ($index % 2),
                    'comment' => $comments[$index % count($comments)],
                    'is_visible' => true,
                ],
            );
        }
    }

    protected function seedProductReports(User $customer, User $admin, $products): void
    {
        $pendingProduct = $products->get(4) ?? $products->first();
        $reviewedProduct = $products->get(5) ?? $products->first();

        ProductReport::query()->updateOrCreate(
            [
                'product_id' => $pendingProduct->id,
                'user_id' => $customer->id,
                'reason' => 'misleading_description',
            ],
            [
                'description' => 'Product image does not match the actual item.',
                'status' => 'pending',
                'handled_by' => null,
                'handled_at' => null,
            ],
        );

        ProductReport::query()->updateOrCreate(
            [
                'product_id' => $reviewedProduct->id,
                'user_id' => $customer->id,
                'reason' => 'quality_issue',
            ],
            [
                'description' => 'Item quality was below expectations.',
                'status' => 'reviewed',
                'handled_by' => $admin->id,
                'handled_at' => now()->subDays(2),
            ],
        );
    }

    protected function seedWishlists(User $customer, $products): void
    {
        foreach ($products->take(4) as $product) {
            Wishlist::query()->updateOrCreate(
                [
                    'user_id' => $customer->id,
                    'product_id' => $product->id,
                ],
            );
        }
    }

    protected function seedOrderRating(User $customer): void
    {
        $deliveredOrder = Order::query()
            ->where('user_id', $customer->id)
            ->where('notes', 'Demo delivered order')
            ->first();

        if (! $deliveredOrder) {
            return;
        }

        OrderRating::query()->updateOrCreate(
            ['order_id' => $deliveredOrder->id],
            [
                'user_id' => $customer->id,
                'rating' => 5,
                'notes' => 'Fast delivery and fresh products.',
            ],
        );
    }
}
