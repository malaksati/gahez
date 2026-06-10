@php
    $primaryAddress = $primaryAddress ?? null;
@endphp

@if ($primaryAddress?->id)
    <input type="hidden" name="address[id]" value="{{ old('address.id', $primaryAddress->id) }}">
@endif

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="address_name" class="form-label">{{ __('messages.Address label') }}</label>
        <input type="text" name="address[name]" id="address_name"
            class="form-control @error('address.name') is-invalid @enderror"
            value="{{ old('address.name', $primaryAddress?->name) }}"
            placeholder="{{ __('messages.Home') }}">
        @error('address.name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="address_phone" class="form-label">{{ __('messages.Phone') }}</label>
        <input type="text" name="address[phone]" id="address_phone"
            class="form-control @error('address.phone') is-invalid @enderror"
            value="{{ old('address.phone', $primaryAddress?->phone) }}">
        @error('address.phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="address_address" class="form-label">{{ __('messages.Address') }}</label>
    <textarea name="address[address]" id="address_address" rows="2"
        class="form-control @error('address.address') is-invalid @enderror"
        placeholder="{{ __('messages.Street address') }}">{{ old('address.address', $primaryAddress?->address) }}</textarea>
    @error('address.address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="address_city" class="form-label">{{ __('messages.City') }}</label>
        <input type="text" name="address[city]" id="address_city"
            class="form-control @error('address.city') is-invalid @enderror"
            value="{{ old('address.city', $primaryAddress?->city) }}">
        @error('address.city')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="address_state" class="form-label">{{ __('messages.State') }}</label>
        <input type="text" name="address[state]" id="address_state"
            class="form-control @error('address.state') is-invalid @enderror"
            value="{{ old('address.state', $primaryAddress?->state) }}">
        @error('address.state')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@include('v1.admin.partials.map-location-picker', [
    'latitude' => old('address.latitude', $primaryAddress?->latitude),
    'longitude' => old('address.longitude', $primaryAddress?->longitude),
])

<input type="hidden" name="address[is_default]" value="0">
<div class="form-check form-switch mb-2">
    <input class="form-check-input" type="checkbox" id="address_is_default" name="address[is_default]" value="1"
        {{ old('address.is_default', $primaryAddress?->is_default ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="address_is_default">{{ __('messages.Default address') }}</label>
</div>
<p class="form-text mb-0 mt-2">{{ __('messages.Customer address hint') }}</p>
