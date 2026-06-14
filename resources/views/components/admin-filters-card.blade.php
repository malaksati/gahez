@props([
    'collapseId' => 'admin-list-filters-' . md5(request()->route()?->getName() ?? 'filters'),
])

<div class="card border-0 shadow-sm mb-3 admin-list-filters-card">
    <div class="card-header bg-transparent border-0 p-0 d-md-none">
        <button
            class="btn btn-outline-secondary btn-sm w-100 mx-3 my-2"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#{{ $collapseId }}"
            aria-expanded="false"
            aria-controls="{{ $collapseId }}"
        >
            <i class="bi bi-funnel me-1"></i>{{ __('messages.Filter') }}
        </button>
    </div>
    <div class="collapse d-md-block" id="{{ $collapseId }}">
        <div class="card-body py-3">
            {{ $slot }}
        </div>
    </div>
</div>
