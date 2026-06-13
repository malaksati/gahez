@php
    $primaryAddress = $primaryAddress ?? null;
    $addresses = $addresses ?? ($primaryAddress ? collect([$primaryAddress]) : collect());
    $isEdit = $isEdit ?? false;
    $selectedId = (int) old('address.id', $primaryAddress?->id ?? $addresses->first()?->id);
    $selectedAddress = $addresses->firstWhere('id', $selectedId) ?? $primaryAddress ?? $addresses->first();
    $addressesJson = $addresses->map(function ($address) {
        return [
            'id' => $address->id,
            'name' => $address->name,
            'phone' => $address->phone,
            'address' => $address->address,
            'city' => $address->city,
            'state' => $address->state,
            'latitude' => $address->latitude,
            'longitude' => $address->longitude,
            'is_default' => (bool) $address->is_default,
        ];
    })->values();
@endphp

@if ($isEdit && $addresses->isNotEmpty())
    <input type="hidden" name="address[id]" id="address_id" value="{{ old('address.id', $selectedAddress?->id) }}">
    <input type="hidden" name="address[name]" id="address_name_hidden" value="{{ old('address.name', $selectedAddress?->name) }}">

    @if ($addresses->count() > 1)
        <div class="mb-3">
            <label for="address_selector" class="form-label">{{ __('messages.Select address') }}</label>
            <select id="address_selector" class="form-select">
                @foreach ($addresses as $addr)
                    <option value="{{ $addr->id }}" @selected($selectedAddress?->id === $addr->id)>
                        {{ $addr->name }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="address_label_display" class="form-label">{{ __('messages.Address label') }}</label>
            <input type="text" id="address_label_display" class="form-control"
                value="{{ old('address.name', $selectedAddress?->name) }}" disabled>
        </div>
        <div class="col-md-6 mb-3">
            <label for="address_phone" class="form-label">{{ __('messages.Phone') }}</label>
            <input type="text" name="address[phone]" id="address_phone"
                class="form-control @error('address.phone') is-invalid @enderror"
                value="{{ old('address.phone', $selectedAddress?->phone) }}">
            @error('address.phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@else
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
@endif

<div class="mb-3">
    <label for="address_address" class="form-label">{{ __('messages.Address') }}</label>
    <textarea name="address[address]" id="address_address" rows="2"
        class="form-control @error('address.address') is-invalid @enderror"
        placeholder="{{ __('messages.Street address') }}">{{ old('address.address', $selectedAddress?->address) }}</textarea>
    @error('address.address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="address_city" class="form-label">{{ __('messages.City') }}</label>
        <input type="text" name="address[city]" id="address_city"
            class="form-control @error('address.city') is-invalid @enderror"
            value="{{ old('address.city', $selectedAddress?->city) }}">
        @error('address.city')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="address_state" class="form-label">{{ __('messages.State') }}</label>
        <input type="text" name="address[state]" id="address_state"
            class="form-control @error('address.state') is-invalid @enderror"
            value="{{ old('address.state', $selectedAddress?->state) }}">
        @error('address.state')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@include('v1.admin.partials.map-location-picker', [
    'latitude' => old('address.latitude', $selectedAddress?->latitude),
    'longitude' => old('address.longitude', $selectedAddress?->longitude),
])

<input type="hidden" name="address[is_default]" value="0">
<div class="form-check form-switch mb-2">
    <input class="form-check-input" type="checkbox" id="address_is_default" name="address[is_default]" value="1"
        {{ old('address.is_default', $selectedAddress?->is_default ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="address_is_default">{{ __('messages.Default address') }}</label>
</div>
<p class="form-text mb-0 mt-2">{{ __('messages.Customer address hint') }}</p>

@if ($isEdit && $addresses->count() > 1)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const selector = document.getElementById('address_selector');
                const addresses = @json($addressesJson);

                function updateMapSummary(lat, lng) {
                    const picker = document.querySelector('[data-map-picker]');
                    if (!picker) return;

                    const summary = picker.querySelector('[data-map-picker-summary]');
                    const preview = picker.querySelector('[data-map-picker-preview]');

                    if (summary && lat && lng) {
                        summary.textContent = 'Location: ' + Number(lat).toFixed(6) + ', ' + Number(lng).toFixed(6);
                        summary.classList.remove('text-muted');
                        summary.classList.add('text-body');
                    }

                    if (preview && lat && lng) {
                        preview.href = 'https://www.google.com/maps?q=' + lat + ',' + lng;
                        preview.classList.remove('d-none');
                    }
                }

                function fillAddress(id) {
                    const addr = addresses.find((a) => a.id === Number(id));
                    if (!addr) return;

                    const idInput = document.getElementById('address_id');
                    const nameInput = document.getElementById('address_name_hidden');
                    const labelDisplay = document.getElementById('address_label_display');

                    if (idInput) idInput.value = addr.id;
                    if (nameInput) nameInput.value = addr.name;
                    if (labelDisplay) labelDisplay.value = addr.name;

                    document.getElementById('address_phone').value = addr.phone ?? '';
                    document.getElementById('address_address').value = addr.address ?? '';
                    document.getElementById('address_city').value = addr.city ?? '';
                    document.getElementById('address_state').value = addr.state ?? '';
                    document.getElementById('address_latitude').value = addr.latitude ?? '';
                    document.getElementById('address_longitude').value = addr.longitude ?? '';
                    document.getElementById('address_is_default').checked = addr.is_default;

                    updateMapSummary(addr.latitude, addr.longitude);
                }

                selector?.addEventListener('change', function () {
                    fillAddress(this.value);
                });
            });
        </script>
    @endpush
@endif
