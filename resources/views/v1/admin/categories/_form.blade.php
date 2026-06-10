@csrf

@include('v1.admin.partials.translatable-inputs', ['field' => 'name', 'label' => __('messages.Name'), 'model' => $category ?? null])

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
