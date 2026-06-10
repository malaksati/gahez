@props([
    'latitudeInputId' => 'address_latitude',
    'longitudeInputId' => 'address_longitude',
    'latitudeName' => 'address[latitude]',
    'longitudeName' => 'address[longitude]',
    'latitude' => null,
    'longitude' => null,
    'defaultLatitude' => '29.3759',
    'defaultLongitude' => '47.9774',
    'label' => __('messages.Location on map'),
])

@php
    $latOldKey = preg_replace('/\[([^\]]+)\]/', '.$1', $latitudeName);
    $lngOldKey = preg_replace('/\[([^\]]+)\]/', '.$1', $longitudeName);
    $latValue = old($latOldKey, $latitude);
    $lngValue = old($lngOldKey, $longitude);
    $hasCoordError = $errors->has($latOldKey) || $errors->has($lngOldKey);
@endphp

@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    @endpush
@endonce

<div
    class="mb-3 map-location-picker @if ($hasCoordError) is-invalid @endif"
    data-map-picker
    data-lat-input="{{ $latitudeInputId }}"
    data-lng-input="{{ $longitudeInputId }}"
    data-default-lat="{{ $defaultLatitude }}"
    data-default-lng="{{ $defaultLongitude }}"
>
    <label class="form-label d-block">{{ $label }}</label>

    <input type="hidden" name="{{ $latitudeName }}" id="{{ $latitudeInputId }}" value="{{ $latValue }}">
    <input type="hidden" name="{{ $longitudeName }}" id="{{ $longitudeInputId }}" value="{{ $lngValue }}">

    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
        <button type="button" class="btn btn-outline-primary btn-sm" data-map-picker-open>
            <i class="bi bi-geo-alt me-1"></i>{{ __('messages.Choose on map') }}
        </button>
        <a
            href="#"
            class="btn btn-outline-secondary btn-sm d-none"
            target="_blank"
            rel="noopener noreferrer"
            data-map-picker-preview
        >
            <i class="bi bi-box-arrow-up-right me-1"></i>{{ __('messages.View on map') }}
        </a>
    </div>

    <p class="small text-muted mb-0" data-map-picker-summary>
        {{ __('messages.No location selected') }}
    </p>

    @error($latOldKey)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    @error($lngOldKey)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <p class="form-text mb-0">{{ __('messages.Map location hint') }}</p>
</div>

@once
    @push('modals')
        <div class="modal fade" id="adminMapPickerModal" tabindex="-1" aria-labelledby="adminMapPickerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminMapPickerModalLabel">{{ __('messages.Choose on map') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.Close') }}"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div id="adminMapPickerMap" class="admin-map-picker-map"></div>
                        <div class="px-3 py-2 border-top small text-muted" data-map-picker-modal-summary></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('messages.Cancel') }}
                        </button>
                        <button type="button" class="btn btn-primary" data-map-picker-confirm>
                            {{ __('messages.Confirm location') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endpush
@endonce
