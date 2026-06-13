<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\V1\Http\Requests\Web\Admin\UpdateSettingsRequest;
use App\V1\Services\SettingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SettingsController extends AdminController
{
    public function __construct(
        protected SettingService $settings,
    ) {}

    public function index(): View
    {
        return view('v1.admin.settings.index');
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        try {
            $this->settings->update([
                'app_name' => $request->validated('app_name'),
                'currency' => $request->validated('currency'),
                'cashback_percentage' => $request->validated('cashback_percentage') ?? 0,
                'point_to_value' => $request->validated('point_to_value') ?? 0,
                'standard_shipping_fee' => $request->input('standard_shipping_fee'),
                'shipping_price_per_km' => $request->validated('shipping_price_per_km') ?? setting('shipping_price_per_km', 0),
                'cart_min_line_count' => $request->validated('cart_min_line_count') ?? setting('cart_min_line_count', 0),
                'cart_min_subtotal' => $request->validated('cart_min_subtotal') ?? setting('cart_min_subtotal', 0),
                'fast_shipping_fee' => $request->validated('fast_shipping_fee') ?? setting('fast_shipping_fee', 0),
                'app_logo' => $request->hasFile('app_logo') ? $request->file('app_logo') : null,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', __('messages.Failed to update settings.'));
        }

        return $this->redirectBackWithSuccess(__('messages.Settings updated successfully.'));
    }
}
