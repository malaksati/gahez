@props(['verified' => false])

@if ($verified)
    <span class="badge bg-status-blue">
        <i class="bi bi-patch-check me-1"></i>{{ __('messages.Verified') }}
    </span>
@else
    <span class="badge bg-danger">
        <i class="bi bi-patch-exclamation me-1"></i>{{ __('messages.Unverified') }}
    </span>
@endif
