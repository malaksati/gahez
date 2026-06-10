@extends('layouts.app')

@section('title', __('messages.Notifications'))
@section('subtitle', __('messages.Your recent alerts and updates'))

@section('page-actions')
    @if (auth()->user()->unreadNotifications()->count() > 0)
        <form method="POST" action="{{ route('v1.admin.notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-check2-all me-1"></i>{{ __('messages.Mark all as read') }}
            </button>
        </form>
    @endif
@endsection

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="list-group list-group-flush">
            @forelse ($notifications as $notification)
                <a
                    href="{{ route('v1.admin.notifications.show', $notification->id) }}"
                    @class([
                        'list-group-item list-group-item-action py-3',
                        'bg-light' => $notification->read_at === null,
                    ])
                >
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div class="min-w-0">
                            <div class="fw-semibold mb-1">{{ $notification->data['title'] ?? __('messages.Notification') }}</div>
                            <p class="mb-0 text-muted small">{{ $notification->data['message'] ?? __('messages.New notification') }}</p>
                        </div>
                        <small class="text-muted text-nowrap">{{ $notification->created_at?->diffForHumans() }}</small>
                    </div>
                </a>
            @empty
                <div class="list-group-item text-center text-muted py-5">
                    <i class="bi bi-bell-slash display-6 d-block mb-2"></i>
                    {{ __('messages.No notifications') }}
                </div>
            @endforelse
        </div>
        @if ($notifications->hasPages())
            <div class="card-footer bg-white">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
