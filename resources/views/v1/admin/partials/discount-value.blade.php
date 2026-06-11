@props([
    'type',
    'amount',
    'offer' => null,
])

@php
    $currency = display_currency();
@endphp

<strong>
    @if ($type === 'percentage')
        {{ format_local_number((float) $amount, 0) }}%
    @elseif ($type === 'bogo' && $offer)
        {{ __('messages.Buy :buy get :bonus', [
            'buy' => (int) ($offer->bogo_buy_quantity ?? 1),
            'bonus' => (int) ($offer->bogo_bonus_quantity ?? 1),
        ]) }}
        @if (($offer->bogo_bonus_discount_type ?? 'percentage') === 'percentage')
            ({{ format_local_number((float) ($offer->bogo_bonus_discount_value ?? 100), 0) }}% {{ __('messages.off') }})
        @else
            ({{ format_local_number((float) ($offer->bogo_bonus_discount_value ?? 0), 2) }}{{ $currency ? ' '.$currency : '' }} {{ __('messages.off') }})
        @endif
    @elseif ($type === 'bogo')
        {{ __('messages.Buy one get one free') }}
    @elseif ($type === 'threshold_gift')
        {{ __('messages.Pick one free gift') }}
    @elseif ($type === 'free_delivery')
        {{ __('messages.Free delivery') }}
    @else
        {{ format_local_number((float) $amount, 2) }}{{ $currency ? ' '.$currency : '' }}
    @endif
</strong>
