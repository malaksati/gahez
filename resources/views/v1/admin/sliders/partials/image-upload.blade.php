@props([
    'existingUrl' => null,
    'required' => true,
])

@php
    $fieldId = 'slider_image';
    $existingUrl = $existingUrl ?: '';
@endphp

<div class="mb-0" data-slider-image-upload>
    <label for="{{ $fieldId }}" class="form-label">{{ __('messages.Image') }}</label>
    <div
        class="file-upload-zone slider-image-upload__zone"
        id="{{ $fieldId }}_zone"
        data-existing-image="{{ $existingUrl }}"
    >
        <div class="upload-zone-content slider-image-upload__content" id="{{ $fieldId }}_content">
            <i class="bi bi-cloud-upload display-4 text-muted mb-3"></i>
            <h5 class="h6 mb-1">{{ __('messages.Drop files here or click to browse') }}</h5>
            <p class="text-muted small mb-0">{{ __('messages.Slider image upload hint') }}</p>
        </div>
        <div class="slider-image-upload__preview" id="{{ $fieldId }}_preview" style="display: none;">
            <img id="{{ $fieldId }}_preview_img" src="" alt="" class="slider-image-upload__preview-img">
            <button
                type="button"
                class="btn btn-danger btn-sm slider-image-upload__remove"
                data-slider-image-remove
                aria-label="{{ __('messages.Remove image') }}"
            >
                <i class="bi bi-trash"></i>
            </button>
        </div>
        <input
            type="file"
            class="d-none"
            id="{{ $fieldId }}"
            name="image"
            accept="image/jpeg,image/png,image/gif,image/webp"
            @if ($required) required @endif
        >
    </div>
  @if (! $required)
        <input type="hidden" name="remove_image" value="0" data-slider-remove-image-flag>
    @endif
    <div id="{{ $fieldId }}_info" class="mt-2 small text-muted" style="display: none;">
        <span id="{{ $fieldId }}_name"></span>
        <span class="mx-1">·</span>
        <span id="{{ $fieldId }}_size"></span>
    </div>
    @error('image')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
