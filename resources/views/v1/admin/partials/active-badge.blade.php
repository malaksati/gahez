@props(['active' => false])

@if ($active)
    <span class="badge bg-success">
        <i class="bi bi-check-circle me-1"></i>{{ __('messages.Active') }}
    </span>
@else
    <span class="badge bg-secondary">
        <i class="bi bi-x-circle me-1"></i>{{ __('messages.Inactive') }}
    </span>
@endif
