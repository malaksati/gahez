<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\V1\Http\Requests\UpdateProfileRequest;
use App\V1\Services\AdminUserService;
use App\V1\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProfileController extends AdminController
{
    public function __construct(
        protected ProfileService $profiles,
        protected AdminUserService $adminUsers,
    ) {}

    public function edit(): View
    {
        $user = auth()->user();
        $user->load('roles', 'permissions');

        return view('v1.admin.profile.edit', [
            'user' => $user,
            'permissionsGrouped' => $this->adminUsers->getPermissionsGrouped(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->profiles->update(
            $request->user(),
            $request->safe()->except(['image', 'remove_image']),
            $request->file('image'),
            $request->boolean('remove_image'),
        );

        return $this->redirectBackWithSuccess(__('messages.Profile updated successfully.'));
    }
}
