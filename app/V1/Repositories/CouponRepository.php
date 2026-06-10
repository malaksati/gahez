<?php

namespace App\V1\Repositories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CouponRepository
{
    protected $model;

    public function __construct(Coupon $coupon)
    {
        $this->model = $coupon;
    }

    public function getAllCoupons(): Collection
    {
        return $this->model::with('orders')::all();
    }

    public function getPaginatedCoupons(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model::query()->withCount('orders');
        // Apply search filter
        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%");
            });
        }

        // Apply type filter
        if (isset($filters['type']) && $filters['type'] !== '') {
            $query->where('type', $filters['type']);
        }

        // Apply is_active filter
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $isActive = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        }

        // Apply date range filters
        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getCouponById(int $id): ?Coupon
    {
        return $this->model::with('orders')->find($id);
    }

    public function getCouponByCode(string $code): ?Coupon
    {
        return $this->model::with('orders')->where('code', $code)->first();
    }

    public function getActiveCoupons(): Collection
    {
        return $this->model::with('orders')->active()->get();
    }

    public function getValidCoupons(): Collection
    {
        return $this->model::with('orders')->valid()->active()->get();
    }

    public function create(array $data): Coupon
    {
        return $this->model::create($data);
    }

    public function update(Coupon $coupon, array $data): bool
    {
        return $coupon->update($data);
    }

    public function delete(Coupon $coupon): bool
    {
        /** @var Model $coupon */
        $model = $coupon;

        return (bool) $model->delete();
    }

    public function forceDelete(Coupon $coupon): bool
    {
        return $coupon->forceDelete();
    }

    public function restore(Coupon $coupon): bool
    {
        return $coupon->restore();
    }
}
