@csrf

@include('v1.admin.partials.translatable-inputs', ['model' => $offer ?? null])

@php
    $selectedRewardIds = old('reward_product_ids', isset($offer) ? $offer->rewardProducts->pluck('product_id')->all() : []);
@endphp

<div
    class="mb-3"
    x-data="{
        offerType: @js(old('type', $offer->type ?? 'fixed')),
        isDiscountType() { return ['fixed', 'percentage'].includes(this.offerType); },
        isBogoType() { return this.offerType === 'bogo'; },
        isThresholdGiftType() { return this.offerType === 'threshold_gift'; },
        isFreeDeliveryType() { return this.offerType === 'free_delivery'; },
        needsOfferable() { return this.isDiscountType() || this.isBogoType(); },
        needsMinCart() { return this.isThresholdGiftType() || this.isFreeDeliveryType(); },
    }"
>
    <label for="type" class="form-label">{{ __('messages.Discount type') }}</label>
    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required x-model="offerType">
        <option value="fixed" @selected(old('type', $offer->type ?? '') === 'fixed')>{{ __('messages.Fixed') }}</option>
        <option value="percentage" @selected(old('type', $offer->type ?? '') === 'percentage')>{{ __('messages.Percentage') }}</option>
        <option value="bogo" @selected(old('type', $offer->type ?? '') === 'bogo')>{{ __('messages.Buy one get one free') }}</option>
        <option value="threshold_gift" @selected(old('type', $offer->type ?? '') === 'threshold_gift')>{{ __('messages.Threshold gift offer') }}</option>
        <option value="free_delivery" @selected(old('type', $offer->type ?? '') === 'free_delivery')>{{ __('messages.Free delivery offer') }}</option>
    </select>
    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror

    <div class="mt-3" x-show="isDiscountType()" x-cloak>
        <label for="value" class="form-label">{{ __('messages.Discount value') }}</label>
        <input type="number" step="0.01" min="0" name="value" id="value"
            value="{{ old('value', $offer->value ?? '') }}"
            class="form-control @error('value') is-invalid @enderror"
            :required="isDiscountType()">
        @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <p class="form-text mb-0" x-show="offerType === 'percentage'">{{ __('messages.Percentage discount hint') }}</p>
        <p class="form-text mb-0" x-show="offerType === 'fixed'" x-cloak>{{ __('messages.Fixed discount hint') }}</p>
    </div>

    <div class="mt-3 border rounded p-3" x-show="isBogoType()" x-cloak>
        <p class="text-muted small mb-3">{{ __('messages.BOGO structure hint') }}</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="bogo_buy_quantity" class="form-label">{{ __('messages.Buy quantity (N)') }} *</label>
                <select name="bogo_buy_quantity" id="bogo_buy_quantity" class="form-select @error('bogo_buy_quantity') is-invalid @enderror" :required="isBogoType()">
                    @foreach ([1, 2] as $qty)
                        <option value="{{ $qty }}" @selected((int) old('bogo_buy_quantity', $offer->bogo_buy_quantity ?? 1) === $qty)>{{ $qty }}</option>
                    @endforeach
                </select>
                @error('bogo_buy_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="bogo_bonus_quantity" class="form-label">{{ __('messages.Bonus quantity (X)') }} *</label>
                <select name="bogo_bonus_quantity" id="bogo_bonus_quantity" class="form-select @error('bogo_bonus_quantity') is-invalid @enderror" :required="isBogoType()">
                    @foreach ([1, 2] as $qty)
                        <option value="{{ $qty }}" @selected((int) old('bogo_bonus_quantity', $offer->bogo_bonus_quantity ?? 1) === $qty)>{{ $qty }}</option>
                    @endforeach
                </select>
                @error('bogo_bonus_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="bogo_bonus_discount_type" class="form-label">{{ __('messages.Bonus discount type') }} *</label>
                <select name="bogo_bonus_discount_type" id="bogo_bonus_discount_type" class="form-select @error('bogo_bonus_discount_type') is-invalid @enderror" :required="isBogoType()">
                    <option value="percentage" @selected(old('bogo_bonus_discount_type', $offer->bogo_bonus_discount_type ?? 'percentage') === 'percentage')>{{ __('messages.Percentage') }}</option>
                    <option value="fixed" @selected(old('bogo_bonus_discount_type', $offer->bogo_bonus_discount_type ?? '') === 'fixed')>{{ __('messages.Fixed') }}</option>
                </select>
                @error('bogo_bonus_discount_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="bogo_bonus_discount_value" class="form-label">{{ __('messages.Bonus discount value') }} *</label>
                <input type="number" step="0.01" min="0" name="bogo_bonus_discount_value" id="bogo_bonus_discount_value"
                    value="{{ old('bogo_bonus_discount_value', $offer->bogo_bonus_discount_value ?? 100) }}"
                    class="form-control @error('bogo_bonus_discount_value') is-invalid @enderror"
                    :required="isBogoType()">
                @error('bogo_bonus_discount_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <p class="form-text mb-0">{{ __('messages.BOGO bonus discount hint') }}</p>
            </div>
        </div>
    </div>

    <div class="mt-3" x-show="isDiscountType()" x-cloak>
        <label for="max_discounted_quantity" class="form-label">{{ __('messages.Max discounted quantity') }}</label>
        <input type="number" min="1" name="max_discounted_quantity" id="max_discounted_quantity"
            value="{{ old('max_discounted_quantity', $offer->max_discounted_quantity ?? '') }}"
            class="form-control @error('max_discounted_quantity') is-invalid @enderror"
            placeholder="{{ __('messages.Leave blank for unlimited') }}">
        @error('max_discounted_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <p class="form-text mb-0">{{ __('messages.Max discounted quantity hint') }}</p>
    </div>

    <div class="mt-3" x-show="needsMinCart()" x-cloak>
        <label for="min_cart_amount" class="form-label">{{ __('messages.Minimum cart amount') }} *</label>
        <input type="number" step="0.01" min="0" name="min_cart_amount" id="min_cart_amount"
            value="{{ old('min_cart_amount', $offer->min_cart_amount ?? '') }}"
            class="form-control @error('min_cart_amount') is-invalid @enderror"
            :required="needsMinCart()">
        @error('min_cart_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <p class="form-text mb-0" x-show="isThresholdGiftType()">{{ __('messages.Threshold gift min cart hint') }}</p>
        <p class="form-text mb-0" x-show="isFreeDeliveryType()" x-cloak>{{ __('messages.Free delivery min cart hint') }}</p>
    </div>

    <div class="mt-3" x-show="isThresholdGiftType()" x-cloak>
        <label for="reward_product_ids" class="form-label">{{ __('messages.Gift product choices') }} *</label>
        <select name="reward_product_ids[]" id="reward_product_ids" class="form-select @error('reward_product_ids') is-invalid @enderror" multiple size="8" :required="isThresholdGiftType()">
            @foreach ($offerablePickerConfig['products'] as $item)
                <option value="{{ $item['id'] }}" @selected(in_array($item['id'], $selectedRewardIds))>{{ $item['name'] }}</option>
            @endforeach
        </select>
        @error('reward_product_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        <p class="form-text mb-0">{{ __('messages.Gift product choices hint') }}</p>
    </div>

    <div
        class="mb-3 mt-3"
        x-data="offerablePicker({{ \Illuminate\Support\Js::from($offerablePickerConfig) }})"
        x-show="needsOfferable()"
        x-cloak
    >
    <label for="offerable_type_key" class="form-label">{{ __('messages.Apply offer to') }}</label>
    <select
        name="offerable_type_key"
        id="offerable_type_key"
        class="form-select @error('offerable_type_key') is-invalid @enderror"
        x-model="typeKey"
        @change="onTypeChange()"
    >
        <option value="product">{{ __('messages.Product') }}</option>
        <option value="category">{{ __('messages.Category') }}</option>
    </select>
    @error('offerable_type_key')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

    <label for="offerable_id" class="form-label mt-3">{{ __('messages.Select item') }}</label>
    @php
        $selectedOfferableId = (string) old('offerable_id', $offer->offerable_id ?? '');
    @endphp
    <select
        name="offerable_id"
        id="offerable_id"
        class="form-select @error('offerable_id') is-invalid @enderror"
        x-show="typeKey === 'product'"
        x-model="selectedId"
        :disabled="typeKey !== 'product'"
    >
        <option value="">{{ __('messages.Select an option') }}</option>
        @foreach ($offerablePickerConfig['products'] as $item)
            <option value="{{ $item['id'] }}" @selected($selectedOfferableId === (string) $item['id'])>{{ $item['name'] }}</option>
        @endforeach
    </select>
    <select
        name="offerable_id"
        id="offerable_id_category"
        class="form-select @error('offerable_id') is-invalid @enderror"
        x-show="typeKey === 'category'"
        x-cloak
        x-model="selectedId"
        :disabled="typeKey !== 'category'"
    >
        <option value="">{{ __('messages.Select an option') }}</option>
        @foreach ($offerablePickerConfig['categories'] as $item)
            <option value="{{ $item['id'] }}" @selected($selectedOfferableId === (string) $item['id'])>{{ $item['name'] }}</option>
        @endforeach
    </select>
    @error('offerable_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    <p class="form-text mb-0" x-show="typeKey === 'product'">{{ __('messages.Offer applies to one product') }}</p>
    <p class="form-text mb-0" x-show="typeKey === 'category' && !isBogoType()" x-cloak>{{ __('messages.Offer applies to one category') }}</p>
    <p class="form-text mb-0" x-show="typeKey === 'category' && isBogoType()" x-cloak>{{ __('messages.BOGO category root hint') }}</p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="start_date" class="form-label">{{ __('messages.Start date') }}</label>
        <input type="date" name="start_date" id="start_date"
            value="{{ old('start_date', isset($offer) && $offer->start_date ? $offer->start_date->format('Y-m-d') : '') }}"
            class="form-control @error('start_date') is-invalid @enderror">
        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <p class="form-text mb-0">{{ __('messages.Leave blank to start immediately') }}</p>
    </div>
    <div class="col-md-6 mb-3">
        <label for="end_date" class="form-label">{{ __('messages.End date') }}</label>
        <input type="date" name="end_date" id="end_date"
            value="{{ old('end_date', isset($offer) && $offer->end_date ? $offer->end_date->format('Y-m-d') : '') }}"
            class="form-control @error('end_date') is-invalid @enderror">
        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <p class="form-text mb-0">{{ __('messages.Leave blank for no end date') }}</p>
    </div>
</div>

<div class="mb-4 form-check">
    <input type="hidden" name="ends_when_out_of_stock" value="0">
    <input type="checkbox" class="form-check-input" name="ends_when_out_of_stock" id="ends_when_out_of_stock" value="1"
        @checked(old('ends_when_out_of_stock', $offer->ends_when_out_of_stock ?? false))>
    <label class="form-check-label" for="ends_when_out_of_stock">{{ __('messages.End offer when out of stock') }}</label>
    <p class="form-text mb-0">{{ __('messages.End offer when out of stock hint') }}</p>
</div>

<div class="mb-4 form-check">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1"
        @checked(old('is_active', $offer->is_active ?? true))>
    <label class="form-check-label" for="is_active">{{ __('messages.Active') }}</label>
    <p class="form-text mb-0">{{ __('messages.Offer active hint') }}</p>
</div>

<div class="mb-4 form-check">
    <input type="hidden" name="show_countdown" value="0">
    <input type="checkbox" class="form-check-input" name="show_countdown" id="show_countdown" value="1"
        @checked(old('show_countdown', $offer->show_countdown ?? false))>
    <label class="form-check-label" for="show_countdown">{{ __('messages.Show countdown on storefront') }}</label>
    <p class="form-text mb-0">{{ __('messages.Show countdown on storefront hint') }}</p>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ __('messages.Save') }}</button>
    <a href="{{ route('v1.admin.offers.index') }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a>
</div>
