@extends('layouts.app')
@section('title', $admin->name)
@section('content')
    <div class="row g-4">
        {{-- Admin Info Card --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <img src="{{ $admin->image }}" alt="{{ $admin->name }}" class="rounded-circle" width="80" height="80" style="object-fit: cover;">
                    </div>
                    <h5 class="mb-1">{{ $admin->name }}</h5>
                    <p class="text-muted mb-2">{{ $admin->email }}</p>
                    @if ($admin->hasRole('super-admin'))
                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">
                            <i class="bi bi-shield-fill-check me-1"></i>{{ __('messages.Super Admin') }}
                        </span>
                    @else
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                            <i class="bi bi-person-gear me-1"></i>{{ __('messages.Admin') }}
                        </span>
                    @endif
                    <div class="mt-3 text-muted small">
                        {{ __('messages.Created') }}: {{ $admin->created_at->format('M d, Y') }}
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top d-flex gap-2 justify-content-center">
                    <a href="{{ route('v1.admin.admin-users.edit', $admin) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>{{ __('messages.Edit') }}
                    </a>
                    @if (!$admin->hasRole('super-admin') && $admin->id !== auth()->id())
                        <form action="{{ route('v1.admin.admin-users.destroy', $admin) }}" method="POST" id="admin-user-delete-form">
                            @csrf
                            @method('DELETE')
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger"
                                data-order-confirm-submit
                                data-confirm-message="{{ e(__('messages.Are you sure you want to delete?')) }}"
                            >
                                <i class="bi bi-trash me-1"></i>{{ __('messages.Delete') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Permissions Card --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">
                        <i class="bi bi-shield-lock me-2"></i>{{ __('messages.Permissions') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if ($admin->hasRole('super-admin'))
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            {{ __('messages.Super Admin has all permissions automatically.') }}
                        </div>
                    @else
                        @php $adminPermissions = $admin->getPermissionNames()->toArray(); @endphp
                        @foreach ($permissionsGrouped as $group => $permissions)
                            <h6 class="text-muted text-uppercase small fw-bold mt-3 mb-2">{{ $group }}</h6>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @foreach ($permissions as $permName => $permLabel)
                                    @if (in_array($permName, $adminPermissions))
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                            <i class="bi bi-check-circle me-1"></i>{{ $permLabel }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">
                                            <i class="bi bi-x-circle me-1"></i>{{ $permLabel }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
