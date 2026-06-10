@props([
    'action',
    'enabled' => true,
    'formId' => null,
])

@php
    $formId = $formId ?? 'notify-customers-' . md5($action);
@endphp

<button
    type="submit"
    form="{{ $formId }}"
    class="btn btn-outline-success"
    @disabled(! $enabled)
    data-bs-toggle="tooltip"
    title="{{ $enabled ? __('messages.Notify customers') : __('messages.Offer or coupon must be active and running') }}"
>
    <i class="bi bi-bell"></i>
</button>
<form
    id="{{ $formId }}"
    action="{{ $action }}"
    method="POST"
    class="d-none"
    data-confirm-message="{{ __('messages.Send notification to all customers?') }}"
>
    @csrf
</form>

