@php
    $selectedMethod = old('payment_method', $selectedMethod ?? 'cash_on_delivery');
    $methodLabels = [
        'cash_on_delivery' => __('messages.Cash on delivery'),
        'wallet' => __('messages.Wallet'),
    ];
@endphp

<div class="mb-3">
    <label for="payment_method" class="form-label">{{ __('messages.Payment method') }} *</label>
    <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
        @foreach (\App\V1\Services\OrderService::PAYMENT_METHODS as $method)
            <option value="{{ $method }}" @selected($selectedMethod === $method)>{{ $methodLabels[$method] }}</option>
        @endforeach
        @if ($selectedMethod && ! in_array($selectedMethod, \App\V1\Services\OrderService::PAYMENT_METHODS, true))
            <option value="{{ $selectedMethod }}" selected>{{ ucfirst(str_replace('_', ' ', $selectedMethod)) }}</option>
        @endif
    </select>
    @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
