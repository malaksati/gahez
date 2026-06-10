<?php

namespace App\V1\Services;

use App\Models\Coupon;
use App\V1\Repositories\CouponRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CouponService
{
    public function __construct(
        protected CouponRepository $coupons,
    ) {}

    public function getAllCoupons(): Collection
    {
        return $this->coupons->getAllCoupons();
    }

    public function getPaginatedCoupons(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->coupons->getPaginatedCoupons($perPage, $filters);
    }

    public function getCouponById(int $id): Coupon
    {
        return $this->coupons->getCouponById($id);
    }

    public function getCouponByCode(string $code): Coupon
    {
        return $this->coupons->getCouponByCode($code);
    }

    public function getActiveCoupons(): Collection
    {
        return $this->coupons->getActiveCoupons();
    }

    public function getValidCoupons(): Collection
    {
        return $this->coupons->getValidCoupons();
    }

    public function create(array $data): Coupon
    {
        return $this->coupons->create($this->normalizeCouponPayload($data));
    }

    public function update(Coupon $coupon, array $data): bool
    {
        return $this->coupons->update($coupon, $this->normalizeCouponPayload($data));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeCouponPayload(array $data): array
    {
        if (($data['type'] ?? '') === 'free_delivery') {
            $data['discount_value'] = 0;
        }

        foreach (['usage_limit', 'usage_limit_per_user', 'start_date', 'end_date'] as $field) {
            if (array_key_exists($field, $data) && ($data[$field] === '' || $data[$field] === null)) {
                $data[$field] = null;
            }
        }

        if (array_key_exists('min_cart_amount', $data) && ($data['min_cart_amount'] === '' || $data['min_cart_amount'] === null)) {
            $data['min_cart_amount'] = 0;
        }

        if (array_key_exists('code', $data) && is_string($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }

        return $data;
    }

    public function delete(Coupon $coupon): bool
    {
        return $this->coupons->delete($coupon);
    }

    public function forceDelete(Coupon $coupon): bool
    {
        return $this->coupons->forceDelete($coupon);
    }

    public function restore(Coupon $coupon): bool
    {
        return $this->coupons->restore($coupon);
    }
}
