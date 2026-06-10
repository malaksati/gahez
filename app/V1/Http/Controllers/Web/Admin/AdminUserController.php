<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\User;
use App\V1\Http\Requests\Web\Admin\StoreAdminUserRequest;
use App\V1\Http\Requests\Web\Admin\UpdateAdminUserRequest;
use App\V1\Services\AdminUserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminUserController extends AdminController
{
    public function __construct(
        protected AdminUserService $adminUsers,
    ) {}

    public function index(Request $request): View|Response
    {
        $admins = $this->adminUsers->getPaginatedAdmins(15, $this->listFilters($request, [
            'search',
        ]));

        return $this->adminListResponse(
            $request,
            'v1.admin.admin-users.index',
            'v1.admin.admin-users.partials.results',
            ['admins' => $admins],
            ['admins' => $admins],
        );
    }

    public function create(): View
    {
        return view('v1.admin.admin-users.create', [
            'permissionsGrouped' => $this->adminUsers->getPermissionsGrouped(),
            'users' => $this->adminUsers->getLinkableUsers(),
        ]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        $this->adminUsers->create($request->validated());

        return $this->redirectWithSuccess('v1.admin.admin-users.index', __('messages.Admin created successfully.'));
    }

    public function show(User $adminUser): View
    {
        $adminUser->load('roles', 'permissions');

        return view('v1.admin.admin-users.show', [
            'admin' => $adminUser,
            'permissionsGrouped' => $this->adminUsers->getPermissionsGrouped(),
        ]);
    }

    public function edit(User $adminUser): View
    {
        $adminUser->load('roles', 'permissions');

        return view('v1.admin.admin-users.edit', [
            'admin' => $adminUser,
            'permissionsGrouped' => $this->adminUsers->getPermissionsGrouped(),
        ]);
    }

    public function update(UpdateAdminUserRequest $request, User $adminUser): RedirectResponse
    {
        if ($adminUser->hasRole('super-admin') && ! auth()->user()->hasRole('super-admin')) {
            return back()->with('error', __('messages.Cannot edit super admin.'));
        }

        $this->adminUsers->update($adminUser, $request->validated());

        return $this->redirectWithSuccess('v1.admin.admin-users.index', __('messages.Admin updated successfully.'));
    }

    public function destroy(User $adminUser): RedirectResponse
    {
        if ($adminUser->hasRole('super-admin')) {
            return back()->with('error', __('messages.Cannot delete super admin.'));
        }

        if ($adminUser->id === auth()->id()) {
            return back()->with('error', __('messages.Cannot delete yourself.'));
        }

        $this->adminUsers->delete($adminUser);

        return $this->redirectWithSuccess('v1.admin.admin-users.index', __('messages.Admin deleted successfully.'));
    }
}
