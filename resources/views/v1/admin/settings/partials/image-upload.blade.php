@props([
    'fieldId',
    'label',
    'hint' => null,
    'existingPath' => null,
])

@php
    $existingUrl = storage_public_url($existingPath) ?? '';
@endphp

<div class="mb-4">
    <label for="{{ $fieldId }}" class="form-label">{{ $label }}</label>
    @if ($hint)
        <p class="form-text mb-2">{{ $hint }}</p>
    @endif
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div
                class="file-upload-zone"
                id="{{ $fieldId }}_zone"
                data-existing-image="{{ $existingUrl }}"
            >
                <div class="upload-zone-content" id="{{ $fieldId }}_content">
                    <i class="bi bi-cloud-upload display-4 text-muted mb-3"></i>
                    <h5 class="h6 mb-1">{{ __('messages.Drop files here or click to browse') }}</h5>
                    <p class="text-muted small mb-0">{{ __('messages.Support for image files (Max 10MB each)') }}</p>
                </div>
                <div class="upload-zone-preview" id="{{ $fieldId }}_preview" style="display: none;">
                    <img id="{{ $fieldId }}_preview_img" src="" alt="" class="preview-image">
                    <div class="preview-overlay">
                        <button type="button" class="btn btn-sm btn-danger" data-settings-remove-image="{{ $fieldId }}">
                            <i class="bi bi-trash"></i> {{ __('messages.Remove') }}
                        </button>
                    </div>
                </div>
                <input
                    type="file"
                    class="d-none"
                    id="{{ $fieldId }}"
                    name="{{ $fieldId }}"
                    accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml,.svg"
                >
            </div>
            <div id="{{ $fieldId }}_info" class="mt-3" style="display: none;">
                <div class="d-flex align-items-center p-2 rounded bg-light">
                    <i class="bi bi-file-earmark-image me-2 text-primary"></i>
                    <div>
                        <div class="fw-medium small" id="{{ $fieldId }}_name"></div>
                        <small class="text-muted" id="{{ $fieldId }}_size"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @error($fieldId)
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
</div>
