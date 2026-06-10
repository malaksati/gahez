@props([
    'action',
    'resetUrl' => null,
])

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ $action }}" class="row g-2 align-items-end">
            {{ $slot }}
            <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-funnel me-1"></i>{{ __('messages.Apply filters') }}
                </button>
                <a href="{{ $resetUrl ?? $action }}" class="btn btn-outline-secondary btn-sm">
                    {{ __('messages.Reset') }}
                </a>
            </div>
        </form>
    </div>
</div>
