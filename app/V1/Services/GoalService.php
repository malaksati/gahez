<?php

namespace App\V1\Services;

use App\Models\Goal;
use App\Models\GoalAchievement;
use App\Models\Order;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GoalService
{
    /**
     * @return array{start: Carbon, end: Carbon}
     */
    public function currentPeriodBounds(string $periodType, ?Carbon $at = null): array
    {
        $at = ($at ?? now())->copy();

        return match ($periodType) {
            Goal::PERIOD_DAILY => [
                'start' => $at->copy()->startOfDay(),
                'end' => $at->copy()->endOfDay(),
            ],
            Goal::PERIOD_WEEKLY => [
                'start' => $at->copy()->startOfWeek(),
                'end' => $at->copy()->endOfWeek(),
            ],
            Goal::PERIOD_MONTHLY => [
                'start' => $at->copy()->startOfMonth(),
                'end' => $at->copy()->endOfMonth(),
            ],
            default => [
                'start' => $at->copy()->startOfDay(),
                'end' => $at->copy()->endOfDay(),
            ],
        };
    }

    public function getPaginatedGoals(int $perPage = 15): LengthAwarePaginator
    {
        return Goal::query()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function create(array $data): Goal
    {
        return Goal::query()->create($data);
    }

    public function update(Goal $goal, array $data): Goal
    {
        $goal->update($data);

        return $goal->fresh();
    }

    public function delete(Goal $goal): bool
    {
        return (bool) $goal->delete();
    }

    public function toggleActive(Goal $goal): Goal
    {
        $goal->update(['is_active' => ! $goal->is_active]);

        return $goal->fresh();
    }

    /**
     * @return Collection<int, Goal>
     */
    public function getRunnableGoals(): Collection
    {
        return Goal::query()
            ->valid()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    public function buildProgressPayload(Goal $goal, User $user): array
    {
        $bounds = $this->currentPeriodBounds($goal->period_type);
        $start = $bounds['start'];
        $end = $bounds['end'];

        $orderTotal = $this->sumUserOrderTotalInPeriod($user->id, $start, $end, forAward: false);
        $minTotal = (float) $goal->min_order_total;
        $progressPercent = $minTotal > 0
            ? min(100, round(($orderTotal / $minTotal) * 100, 2))
            : 0;

        $achievement = GoalAchievement::query()
            ->where('goal_id', $goal->id)
            ->where('user_id', $user->id)
            ->whereDate('period_start', $start->toDateString())
            ->first();

        return [
            'goal' => $goal,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'order_total' => round($orderTotal, 2),
            'min_order_total' => $minTotal,
            'reward_amount' => (float) $goal->reward_amount,
            'progress_percent' => $progressPercent,
            'is_achieved' => $achievement !== null,
            'achieved_at' => $achievement?->awarded_at?->toIso8601String(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getActiveGoalsWithProgressForUser(User $user): array
    {
        return $this->getRunnableGoals()
            ->map(fn (Goal $goal) => $this->buildProgressPayload($goal, $user))
            ->values()
            ->all();
    }

    public function evaluateGoalsForDeliveredOrder(Order $order): void
    {
        if (! $order->user_id) {
            return;
        }

        $user = $order->user ?? User::query()->find($order->user_id);

        if (! $user) {
            return;
        }

        foreach ($this->getRunnableGoals() as $goal) {
            $this->awardGoalIfEligible($goal, $user);
        }
    }

    public function awardGoalIfEligible(Goal $goal, User $user): bool
    {
        $bounds = $this->currentPeriodBounds($goal->period_type);
        $start = $bounds['start'];
        $end = $bounds['end'];

        $orderTotal = $this->sumUserOrderTotalInPeriod($user->id, $start, $end, forAward: true);
        $minTotal = (float) $goal->min_order_total;
        $rewardAmount = (float) $goal->reward_amount;

        if ($minTotal <= 0 || $rewardAmount <= 0 || $orderTotal < $minTotal) {
            return false;
        }

        return DB::transaction(function () use ($goal, $user, $start, $end, $orderTotal, $rewardAmount) {
            $exists = GoalAchievement::query()
                ->where('goal_id', $goal->id)
                ->where('user_id', $user->id)
                ->whereDate('period_start', $start->toDateString())
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                return false;
            }

            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $walletAfter = round((float) $lockedUser->wallet + $rewardAmount, 2);

            $lockedUser->update(['wallet' => $walletAfter]);

            WalletTransaction::query()->create([
                'user_id' => $lockedUser->id,
                'type' => 'addition',
                'amount' => $rewardAmount,
                'balance_after' => $walletAfter,
                'notes' => __('messages.Goal wallet reward', [
                    'name' => $goal->getTranslation('name', app()->getLocale(), false)
                        ?: $goal->getTranslation('name', 'en', false)
                        ?: __('messages.Goal'),
                    'amount' => format_local_number($orderTotal, 2).' '.display_currency(),
                ]),
            ]);

            GoalAchievement::query()->create([
                'goal_id' => $goal->id,
                'user_id' => $lockedUser->id,
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'order_total' => $orderTotal,
                'reward_amount' => $rewardAmount,
                'awarded_at' => now(),
            ]);

            return true;
        });
    }

    protected function sumUserOrderTotalInPeriod(
        int $userId,
        Carbon $start,
        Carbon $end,
        bool $forAward = false,
    ): float {
        $query = Order::query()
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$start, $end]);

        if ($forAward) {
            $query->where('status', 'delivered');
        } else {
            $query->whereNotIn('status', ['cancelled', 'refunded']);
        }

        return (float) $query->sum('sub_total');
    }
}
