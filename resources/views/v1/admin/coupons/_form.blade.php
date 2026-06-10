@csrf

<div
    x-data="{
        couponType: @js(old('type', $coupon->type ?? 'fixed')),
        isDiscountType() { return ['fixed', 'percentage'].includes(this.couponType); },
        isFreeDeliveryType() { return this.couponType === 'free_delivery'; },
    }"
>
    <div class="mb-3">
        <label for="code" class="form-label">{{ __('messages.Code') }}</label>
        <input
            type="text"
            name="code"
            id="code"
            value="{{ old('code', $coupon->code ?? '') }}"
            class="form-control @error('code') is-invalid @enderror"
            required
        >
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <p class="form-text mb-0">{{ __('messages.Coupon code hint') }}</p>
    </div>

    <div class="mb-3">
        <label for="type" class="form-label">{{ __('messages.Discount type') }}</label>
        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required x-model="couponType">
            <option value="fixed" @selected(old('type', $coupon->type ?? '') === 'fixed')>{{ __('messages.Fixed') }}</option>
            <option value="percentage" @selected(old('type', $coupon->type ?? '') === 'percentage')>{{ __('messages.Percentage') }}</option>
            <option value="free_delivery" @selected(old('type', $coupon->type ?? '') === 'free_delivery')>{{ __('messages.Free delivery coupon') }}</option>
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <p class="form-text mb-0">{{ __('messages.Coupon type hint') }}</p>
    </div>

    <div class="mb-3" x-show="isDiscountType()" x-cloak>
        <label for="discount_value" class="form-label">{{ __('messages.Discount value') }}</label>
        <input
            type="number"
            step="0.01"
            min="0"
            name="discount_value"
            id="discount_value"
            value="{{ old('discount_value', $coupon->discount_value ?? '') }}"
            class="form-control @error('discount_value') is-invalid @enderror"
            :required="isDiscountType()"
        >
        @error('discount_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <p class="form-text mb-0" x-show="couponType === 'percentage'">{{ __('messages.Coupon percentage discount hint') }}</p>
        <p class="form-text mb-0" x-show="couponType === 'fixed'" x-cloak>{{ __('messages.Coupon fixed discount hint') }}</p>
    </div>

    <div class="mb-3">
        <label for="min_cart_amount" class="form-label">{{ __('messages.Min cart amount') }}</label>
        <input
            type="number"
            step="0.01"
            min="0"
            name="min_cart_amount"
            id="min_cart_amount"
            value="{{ old('min_cart_amount', $coupon->min_cart_amount ?? '') }}"
            class="form-control @error('min_cart_amount') is-invalid @enderror"
            placeholder="0"
        >
        @error('min_cart_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <p class="form-text mb-0" x-show="isFreeDeliveryType()" x-cloak>{{ __('messages.Coupon free delivery min cart hint') }}</p>
        <p class="form-text mb-0" x-show="!isFreeDeliveryType()">{{ __('messages.Coupon min cart hint') }}</p>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="usage_limit" class="form-label">{{ __('messages.Total order limit') }}</label>
            <input
                type="number"
                min="1"
                name="usage_limit"
                id="usage_limit"
                value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}"
                class="form-control @error('usage_limit') is-invalid @enderror"
                placeholder="{{ __('messages.Unlimited') }}"
            >
            @error('usage_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <p class="form-text mb-0">{{ __('messages.Coupon total order limit hint') }}</p>
            @if (isset($coupon) && $coupon->exists)
                <p class="form-text mb-0 text-muted">
                    {{ __('messages.Orders used') }}: {{ $coupon->totalOrdersUsed() }}
                </p>
            @endif
        </div>
        <div class="col-md-6 mb-3">
            <label for="usage_limit_per_user" class="form-label">{{ __('messages.Usage limit per user') }}</label>
            <input
                type="number"
                min="1"
                name="usage_limit_per_user"
                id="usage_limit_per_user"
                value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user ?? '') }}"
                class="form-control @error('usage_limit_per_user') is-invalid @enderror"
                placeholder="{{ __('messages.Unlimited') }}"
            >
            @error('usage_limit_per_user')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <p class="form-text mb-0">{{ __('messages.Coupon usage limit per user hint') }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="start_date" class="form-label">{{ __('messages.Start date') }}</label>
            <input
                type="date"
                name="start_date"
                id="start_date"
                value="{{ old('start_date', isset($coupon) && $coupon->start_date ? $coupon->start_date->format('Y-m-d') : '') }}"
                class="form-control @error('start_date') is-invalid @enderror"
            >
            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <p class="form-text mb-0">{{ __('messages.Leave blank to start immediately') }}</p>
        </div>
        <div class="col-md-6 mb-3">
            <label for="end_date" class="form-label">{{ __('messages.End date') }}</label>
            <input
                type="date"
                name="end_date"
                id="end_date"
                value="{{ old('end_date', isset($coupon) && $coupon->end_date ? $coupon->end_date->format('Y-m-d') : '') }}"
                class="form-control @error('end_date') is-invalid @enderror"
            >
            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <p class="form-text mb-0">{{ __('messages.Leave blank for no end date') }}</p>
        </div>
    </div>

    <div class="mb-4 form-check">
        <input type="hidden" name="first_order_only" value="0">
        <input type="checkbox" class="form-check-input" name="first_order_only" id="first_order_only" value="1"
            @checked(old('first_order_only', $coupon->first_order_only ?? false))>
        <label class="form-check-label" for="first_order_only">{{ __('messages.First order only') }}</label>
        <p class="form-text mb-0">{{ __('messages.Coupon first order only hint') }}</p>
    </div>

    <div class="mb-4 form-check">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1"
            @checked(old('is_active', $coupon->is_active ?? true))>
        <label class="form-check-label" for="is_active">{{ __('messages.Active') }}</label>
        <p class="form-text mb-0">{{ __('messages.Coupon active hint') }}</p>
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ __('messages.Save') }}</button>
    <a href="{{ route('v1.admin.coupons.index') }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a>
</div>
