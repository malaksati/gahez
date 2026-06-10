@if ($admins->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.Name') }}</th>
                    <th>{{ __('messages.Email') }}</th>
                    <th>{{ __('messages.Role') }}</th>
                    <th>{{ __('messages.Permissions') }}</th>
                    <th>{{ __('messages.Created') }}</th>
                    <th class="text-end" style="width: 120px;">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($admins as $admin)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $admin->image }}" alt="{{ $admin->name }}" class="rounded-circle" width="36" height="36" style="object-fit: cover;">
                                <strong>{{ $admin->name }}</strong>
                            </div>
                        </td>
                        <td class="text-muted">{{ $admin->email }}</td>
                        <td>
                            @if ($admin->hasRole('super-admin'))
                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                    <i class="bi bi-shield-fill-check me-1"></i>{{ __('messages.Super Admin') }}
                                </span>
                            @else
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-person-gear me-1"></i>{{ __('messages.Admin') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if ($admin->hasRole('super-admin'))
                                <span class="badge bg-success bg-opacity-10 text-success">{{ __('messages.All permissions') }}</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                    {{ $admin->permissions->count() }} {{ __('messages.permissions') }}
                                </span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $admin->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            @include('v1.admin.partials.table-actions', [
                                'showUrl' => route('v1.admin.admin-users.show', $admin),
                                'editUrl' => route('v1.admin.admin-users.edit', $admin),
                                'destroyUrl' => (!$admin->hasRole('super-admin') && $admin->id !== auth()->id())
                                    ? route('v1.admin.admin-users.destroy', $admin)
                                    : null,
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 px-3 pb-3">{{ $admins->links() }}</div>
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'people',
        'message' => __('messages.No admin users found.'),
        'createUrl' => route('v1.admin.admin-users.create'),
        'createLabel' => __('messages.New admin'),
    ])
@endif
