@php
    $user = auth()->user();
    $unreadCount = $user?->unreadNotifications()->count() ?? 0;
    $recentNotifications = $user?->notifications()->latest()->limit(5)->get() ?? collect();
@endphp

<div class="dropdown notifications-dropdown me-2" data-live-notifications>
    <button
        class="btn btn-outline-secondary position-relative"
        type="button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        aria-label="{{ __('messages.Notifications') }}"
    >
        <i class="bi bi-bell"></i>
        <span
            @class([
                'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger',
                'd-none' => $unreadCount === 0,
            ])
            data-notifications-badge
        >
            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
        </span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 320px; max-width: 360px;" data-notifications-menu>
        <li><h6 class="dropdown-header">{{ __('messages.Notifications') }}</h6></li>
        @forelse ($recentNotifications as $notification)
            <li data-notification-item data-notification-id="{{ $notification->id }}">
                <a
                    href="{{ route('v1.admin.notifications.show', $notification->id) }}"
                    @class(['dropdown-item py-2', 'fw-semibold' => $notification->read_at === null])
                >
                    <div class="small text-muted">{{ $notification->created_at?->diffForHumans() }}</div>
                    <div class="text-truncate">{{ $notification->data['message'] ?? __('messages.New notification') }}</div>
                </a>
            </li>
        @empty
            <li data-notifications-empty>
                <span class="dropdown-item text-muted">{{ __('messages.No notifications') }}</span>
            </li>
        @endforelse
        <li data-notifications-divider><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item text-center small" href="{{ route('v1.admin.notifications.index') }}">
                {{ __('messages.View all notifications') }}
            </a>
        </li>
        <li @class(['d-none' => $unreadCount === 0]) data-notifications-mark-all>
            <form method="POST" action="{{ route('v1.admin.notifications.mark-all-read') }}" data-notifications-mark-all-form>
                @csrf
                <button type="submit" class="dropdown-item text-center small text-primary">
                    {{ __('messages.Mark all as read') }}
                </button>
            </form>
        </li>
    </ul>
</div>
