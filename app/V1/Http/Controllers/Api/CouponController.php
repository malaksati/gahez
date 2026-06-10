<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\V1\Http\Requests\Api\StoreCouponRequest;
use App\V1\Http\Requests\Api\UpdateCouponRequest;
use App\V1\Http\Resources\Api\CouponResource;
use App\V1\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(
        protected CouponService $couponService,
    ) {}

    public function index(Request $request)
    {
        return CouponResource::collection(
            $this->couponService->getValidCoupons()
        );
    }

    public function store(StoreCouponRequest $request)
    {
        $coupon = $this->couponService->create($request->validated());

        return (new CouponResource($coupon))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateCouponRequest $request, int $id)
    {
        $coupon = $this->couponService->getCouponById($id);

        $this->couponService->update($coupon, $request->validated());

        return new CouponResource($coupon->fresh());
    }

    public function destroy(int $id)
    {
        $coupon = $this->couponService->getCouponById($id);

        $this->couponService->delete($coupon);

        return response()->noContent();
    }
}
