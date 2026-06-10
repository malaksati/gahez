@props([
    'showUrl' => null,
    'editUrl' => null,
    'destroyUrl' => null,
    'notifyUrl' => null,
    'notifyEnabled' => true,
    'confirm' => null,
])

@php
    $confirmMessage = $confirm ?? __('messages.Are you sure you want to delete?');
    $actionKey = md5(($destroyUrl ?? '') . ($editUrl ?? '') . ($showUrl ?? '') . ($notifyUrl ?? ''));
    $deleteFormId = 'table-action-delete-' . $actionKey;
    $notifyFormId = 'table-action-notify-' . $actionKey;
@endphp

<div class="btn-group btn-group-sm table-actions" role="group">
    @if ($notifyUrl)
        @include('v1.admin.partials.notify-customers-button', [
            'action' => $notifyUrl,
            'enabled' => $notifyEnabled,
            'formId' => $notifyFormId,
        ])
    @endif
    @if ($showUrl)
        <a
            href="{{ $showUrl }}"
            class="btn btn-outline-info"
            data-bs-toggle="tooltip"
            title="{{ __('messages.View') }}"
        >
            <i class="bi bi-eye"></i>
        </a>
    @endif
    @if ($editUrl)
        <a
            href="{{ $editUrl }}"
            class="btn btn-outline-primary"
            data-bs-toggle="tooltip"
            title="{{ __('messages.Edit') }}"
        >
            <i class="bi bi-pencil"></i>
        </a>
    @endif
    @if ($destroyUrl)
        <button
            type="submit"
            form="{{ $deleteFormId }}"
            class="btn btn-outline-danger"
            data-bs-toggle="tooltip"
            title="{{ __('messages.Delete') }}"
        >
            <i class="bi bi-trash"></i>
        </button>
    @endif
</div>
@if ($notifyUrl)
    <form
        id="{{ $notifyFormId }}"
        action="{{ $notifyUrl }}"
        method="POST"
        class="d-none"
        data-confirm-message="{{ __('messages.Send notification to all customers?') }}"
    >
        @csrf
    </form>
@endif
@if ($destroyUrl)
    <form
        id="{{ $deleteFormId }}"
        action="{{ $destroyUrl }}"
        method="POST"
        class="d-none"
        data-confirm-message="{{ $confirmMessage }}"
    >
        @csrf
        @method('DELETE')
    </form>
@endif
