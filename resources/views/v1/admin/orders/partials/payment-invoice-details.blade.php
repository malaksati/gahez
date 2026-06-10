@php
    $methodLabels = [
        'cash_on_delivery' => __('messages.Cash on delivery'),
        'wallet' => __('messages.Wallet'),
    ];

    $paymentMethodLabel = $order->payment_method
        ? ($methodLabels[$order->payment_method] ?? ucfirst(str_replace('_', ' ', $order->payment_method)))
        : '—';

    $paidAtLabel = $order->paid_at?->format('d-m-Y H:i') ?? '—';
@endphp

<div class="label-box">{{ __('messages.Payment details') }}</div>
<table class="info-table">
    <tr>
        <td>
            <strong>{{ __('messages.Payment method') }}:</strong>
            <span class="muted">{{ $paymentMethodLabel }}</span>
        </td>
        <td class="col-end">
            <strong>{{ __('messages.Paid at') }}:</strong>
            <span class="muted"><span class="ltr">{{ $paidAtLabel }}</span></span>
        </td>
    </tr>
</table>
