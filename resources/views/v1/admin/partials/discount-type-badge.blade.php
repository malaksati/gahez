@props(['type'])

@if ($type === 'percentage')
    <span class="badge bg-info">{{ __('messages.Percentage') }}</span>
@elseif ($type === 'bogo')
    <span class="badge bg-success">{{ __('messages.Buy one get one free') }}</span>
@elseif ($type === 'threshold_gift')
    <span class="badge bg-warning text-dark">{{ __('messages.Threshold gift offer') }}</span>
@elseif ($type === 'free_delivery')
    <span class="badge bg-info">{{ __('messages.Free delivery offer') }}</span>
@else
    <span class="badge bg-primary">{{ __('messages.Fixed') }}</span>
@endif
