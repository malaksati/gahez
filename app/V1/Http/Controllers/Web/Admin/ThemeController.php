<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\V1\Http\Requests\Web\Admin\UpdateStoreThemeRequest;
use App\V1\Services\SettingService;
use App\V1\Support\StoreTheme;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ThemeController extends AdminController
{
    public function __construct(
        protected SettingService $settings,
    ) {}

    public function index(): View
    {
        return view('v1.admin.theme.index', [
            'theme' => StoreTheme::fromSettings(),
            'layoutOptions' => StoreTheme::layoutOptions(),
            'fontOptions' => StoreTheme::fontOptions(),
        ]);
    }

    public function update(UpdateStoreThemeRequest $request): RedirectResponse
    {
        $this->settings->updateStoreTheme($request->validated());

        return $this->redirectBackWithSuccess(__('messages.Store theme updated successfully.'));
    }
}
