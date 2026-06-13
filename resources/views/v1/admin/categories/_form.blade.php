@csrf

@include('v1.admin.partials.translatable-inputs', ['field' => 'name', 'label' => __('messages.Name'), 'model' => $category ?? null])

@php
    $isEdit = isset($category);
    $hasImage = $isEdit && $category->getRawOriginal('image');
@endphp

<div class="mt-4">
    <label for="category_image" class="form-label">{{ __('messages.Image') }}</label>
    @if ($hasImage)
        <div class="mb-3">
            <img src="{{ $category->image }}" alt="{{ $category->getTranslation('name', 'en') }}"
                id="category_image_preview" class="rounded border" width="120" height="120" style="object-fit: cover;">
        </div>
    @else
        <img id="category_image_preview" src="" alt="" class="rounded border mb-3 d-none" width="120" height="120" style="object-fit: cover;">
    @endif
    <input type="file" name="image" id="category_image" accept="image/*"
        class="form-control @error('image') is-invalid @enderror">
    @error('image')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    @if ($hasImage)
        <input type="hidden" name="remove_image" value="0">
        <div class="form-check mt-2">
            <input type="checkbox" class="form-check-input" name="remove_image" id="remove_category_image" value="1"
                {{ old('remove_image') ? 'checked' : '' }}>
            <label class="form-check-label" for="remove_category_image">{{ __('messages.Remove image') }}</label>
        </div>
    @endif
</div>

<div class="mt-4">
    <label for="sort_order" class="form-label">{{ __('messages.Sort order') }}</label>
    <input type="number" min="1" id="sort_order" name="sort_order"
        class="form-control @error('sort_order') is-invalid @enderror"
        value="{{ old('sort_order', isset($category) ? $category->sort_order : '') }}"
        placeholder="{{ __('messages.Leave blank for last position') }}">
    <p class="form-text mb-0">{{ __('messages.Category sort order shift hint') }}</p>
    @error('sort_order')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mt-4">
    <label for="parent_id" class="form-label">{{ __('messages.Parent category') }}</label>
    <select name="parent_id" id="parent_id" class="form-control">
        <option value="">{{ __('messages.— None —') }}</option>
        @foreach ($parentCategories as $parent)
            <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id ?? null) == $parent->id)>
                {{ $parent->getTranslation('name', 'en') }}
            </option>
        @endforeach
    </select>
</div>

<div class="mt-4 d-flex gap-4">
    <div class="form-check">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
            @checked(old('is_active', $category->is_active ?? true))>
        <label class="form-check-label" for="is_active">{{ __('messages.Active') }}</label>
    </div>
    <div class="form-check">
        <input type="hidden" name="is_featured" value="0">
        <input type="checkbox" name="is_featured" value="1" id="is_featured" class="form-check-input"
            @checked(old('is_featured', $category->is_featured ?? false))>
        <label class="form-check-label" for="is_featured">{{ __('messages.Featured') }}</label>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ __('messages.Save') }}</button>
    <a href="{{ route('v1.admin.categories.index') }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('category_image');
            const preview = document.getElementById('category_image_preview');

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

                const removeCheckbox = document.getElementById('remove_category_image');
                if (removeCheckbox) {
                    removeCheckbox.checked = false;
                }
            });
        });
    </script>
@endpush
