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
                'report_hero_order_amount' => $request->validated('report_hero_order_amount') ?? setting('report_hero_order_amount', 100),
                'report_lower_value_order_amount' => $request->validated('report_lower_value_order_amount') ?? setting('report_lower_value_order_amount', 20),
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
