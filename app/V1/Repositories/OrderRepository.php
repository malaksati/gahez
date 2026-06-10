<?php

namespace App\V1\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository
{
    protected $model;
    public function __construct(Order $order)
    {
        $this->model = $order;
    }
    public function getPaginatedOrders(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model::query()
            ->with([
                'user',
                'coupon',
                'address',
                'logs' => fn ($logQuery) => $logQuery->with('user')->latest()->limit(1),
            ])
            ->withCount('logs');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $statusFilter = (string) $filters['status'];
            if (str_contains($statusFilter, ',')) {
                $statuses = array_values(array_filter(array_map('trim', explode(',', $statusFilter))));
                $query->whereIn('status', $statuses);
            } else {
                $query->where('status', $statusFilter);
            }
        }

        if (isset($filters['payment_status']) && $filters['payment_status'] !== '') {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['payment_method']) && $filters['payment_method'] !== '') {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (isset($filters['refund_status']) && $filters['refund_status'] !== '') {
            $query->where('refund_status', $filters['refund_status']);
        }

        if (isset($filters['user_id']) && $filters['user_id'] !== '') {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['coupon_id']) && $filters['coupon_id'] !== '') {
            $query->where('coupon_id', $filters['coupon_id']);
        }

        if (isset($filters['address_id']) && $filters['address_id'] !== '') {
            $query->where('address_id', $filters['address_id']);
        }

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (isset($filters['min_total']) && $filters['min_total'] !== '') {
            $query->where('total', '>=', $filters['min_total']);
        }

        if (isset($filters['max_total']) && $filters['max_total'] !== '') {
            $query->where('total', '<=', $filters['max_total']);
        }

        $sort = (string) ($filters['sort'] ?? 'latest');

        match ($sort) {
            'oldest' => $query->oldest(),
            'total_asc' => $query->orderBy('total', 'asc'),
            'total_desc' => $query->orderBy('total', 'desc'),
            default => $query->latest(),
        };

        return $query->paginate($perPage);
    }
    public function getOrderById(int $id): ?Order
    {
        return $this->model::with([
            'user',
            'coupon',
            'address',
            'items.product',
            'items.variant',
            'logs.user',
        ])->find($id);
    }
    public function getOrdersByUser(int $userId): Collection
    {
        return $this->model::query()->where('user_id', '=', $userId, 'and')
            ->with([
                'coupon',
                'address',
            ])
            ->latest()
            ->get();
    }
    public function getPaginatedOrdersForUser(int $userId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $filters['user_id'] = $userId;

        return $this->getPaginatedOrders($perPage, $filters);
    }
    public function getOrderByIdForUser(int $id, int $userId): ?Order
    {
        return Order::with([
            'user',
            'coupon',
            'address',
            'items.product',
            'items.variant',
        ])->where('user_id', $userId)->find($id);
    }
    public function create(array $data): Order
    {
        return $this->model::create($data);
    }
    public function update(Order $order, array $data): bool
    {
        return $order->update($data);
    }
    public function delete(Order $order): bool
    {
        /** @var Model $order */
        $model = $order;
        return (bool) $model->delete();
    }
    public function forceDelete(Order $order): bool
    {
        return $order->forceDelete();
    }
    public function restore(Order $order): bool
    {
        return $order->restore();
    }
}
