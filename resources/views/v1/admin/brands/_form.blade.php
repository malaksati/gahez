@csrf
@include('v1.admin.partials.translatable-inputs', ['model' => $brand ?? null])

@php
    $isEdit = isset($brand);
    $hasImage = $isEdit && $brand->getRawOriginal('image');
@endphp

<div class="mt-4">
    <label for="brand_image" class="form-label">{{ __('messages.Image') }}</label>
    @if ($hasImage)
        <div class="mb-3">
            <img src="{{ $brand->image }}" alt="{{ $brand->getTranslation('name', 'en') }}"
                id="brand_image_preview" class="rounded border" width="120" height="120" style="object-fit: cover;">
        </div>
    @else
        <img id="brand_image_preview" src="" alt="" class="rounded border mb-3 d-none" width="120" height="120" style="object-fit: cover;">
    @endif
    <input type="file" name="image" id="brand_image" accept="image/*"
        class="form-control @error('image') is-invalid @enderror">
    @error('image')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    @if ($hasImage)
        <input type="hidden" name="remove_image" value="0">
        <div class="form-check mt-2">
            <input type="checkbox" class="form-check-input" name="remove_image" id="remove_brand_image" value="1"
                {{ old('remove_image') ? 'checked' : '' }}>
            <label class="form-check-label" for="remove_brand_image">{{ __('messages.Remove image') }}</label>
        </div>
    @endif
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ __('messages.Save') }}</button>
    <a href="{{ route('v1.admin.brands.index') }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('brand_image');
            const preview = document.getElementById('brand_image_preview');

            if (!input || !preview) {
                return;
            }

            input.addEventListener('change', function () {
                const file = this.files?.[0];

                if (!file) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = (event) => {
                    preview.src = event.target?.result ?? preview.src;
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);

                const removeCheckbox = document.getElementById('remove_brand_image');
                if (removeCheckbox) {
                    removeCheckbox.checked = false;
                }
            });
        });
    </script>
@endpush
