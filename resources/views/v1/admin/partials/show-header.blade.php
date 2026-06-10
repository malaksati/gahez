@props([
    'indexRoute',
    'indexLabel',
    'title',
    'editRoute' => null,
    'editLabel' => null,
    'destroyRoute' => null,
    'destroyConfirm' => null,
])

@php
    $editLabel = $editLabel ?? __('messages.Edit');
    $destroyConfirm = $destroyConfirm ?? __('messages.Are you sure you want to delete?');
    $deleteFormId = $destroyRoute ? 'show-delete-' . md5($destroyRoute) : null;
@endphp

<div class="mb-4">
    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('v1.admin.dashboard') }}">{{ __('messages.Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route($indexRoute) }}">{{ $indexLabel }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h2 class="h4 mb-2">{{ $title }}</h2>
            @if (isset($badges))
                <div class="d-flex flex-wrap gap-2">{!! $badges !!}</div>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if ($editRoute)
                <a href="{{ $editRoute }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>{{ $editLabel }}
                </a>
            @endif
            @if ($destroyRoute)
                <button type="submit" form="{{ $deleteFormId }}" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>{{ __('messages.Delete') }}
                </button>
            @endif
            <a href="{{ route($indexRoute) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>{{ __('messages.Back') }}
            </a>
        </div>
    </div>
</div>

@if ($destroyRoute)
    <form
        id="{{ $deleteFormId }}"
        action="{{ $destroyRoute }}"
        method="POST"
        class="d-none"
        data-confirm-message="{{ $destroyConfirm }}"
    >
        @csrf
        @method('DELETE')
    </form>
@endif
