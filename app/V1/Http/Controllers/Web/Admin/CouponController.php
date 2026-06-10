<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\Coupon;
use App\V1\Http\Requests\Web\Admin\StoreCouponRequest;
use App\V1\Http\Requests\Web\Admin\UpdateCouponRequest;
use App\V1\Services\CouponService;
use App\V1\Services\CustomerBroadcastService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CouponController extends AdminController
{
    public function __construct(
        protected CouponService $coupons,
        protected CustomerBroadcastService $customerBroadcasts,
    ) {}

    public function index(): View
    {
        return view('v1.admin.coupons.index', [
            'coupons' => $this->coupons->getPaginatedCoupons(),
        ]);
    }

    public function create(): View
    {
        return view('v1.admin.coupons.create', [
            'coupon' => new Coupon([
                'type' => 'fixed',
                'is_active' => true,
            ]),
        ]);
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $this->coupons->create($request->validated());

        return $this->redirectWithSuccess('v1.admin.coupons.index', 'Coupon created successfully.');
    }

    public function show(Coupon $coupon): View
    {
        $coupon->loadCount('orders');

        return view('v1.admin.coupons.show', [
            'coupon' => $coupon,
        ]);
    }

    public function edit(Coupon $coupon): View
    {
        return view('v1.admin.coupons.edit', [
            'coupon' => $coupon,
        ]);
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $this->coupons->update($coupon, $request->validated());

        return $this->redirectWithSuccess('v1.admin.coupons.index', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $this->coupons->delete($coupon);

        return $this->redirectWithSuccess('v1.admin.coupons.index', 'Coupon deleted successfully.');
    }

    public function notifyCustomers(Coupon $coupon): RedirectResponse
    {
        $sent = $this->customerBroadcasts->notifyCustomersAboutCoupon($coupon);

        return $this->redirectBackWithSuccess(
            __('messages.Coupon notification sent to :count customers.', ['count' => $sent]),
        );
    }
}
