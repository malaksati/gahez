@props([
    'field' => 'name',
    'label' => null,
    'model' => null,
    'type' => 'text',
    'rows' => 4,
])

@php
    $label = $label ?? __('messages.Name');
    $enValue = old($field.'.en', $model?->getTranslation($field, 'en', false));
    $arValue = old($field.'.ar', $model?->getTranslation($field, 'ar', false));
    $inputClass = 'form-control'.($errors->has($field.'.en') ? ' is-invalid' : '');
    $inputClassAr = 'form-control'.($errors->has($field.'.ar') ? ' is-invalid' : '');
@endphp

<div class="mb-3">
    <label for="{{ $field }}_en" class="form-label">{{ $label }} ({{ __('messages.English') }})</label>
    @if ($type === 'textarea')
        <textarea name="{{ $field }}[en]" id="{{ $field }}_en" rows="{{ $rows }}" class="{{ $inputClass }}">{{ $enValue }}</textarea>
    @else
        <input type="text" name="{{ $field }}[en]" id="{{ $field }}_en" value="{{ $enValue }}" class="{{ $inputClass }}">
    @endif
    @error($field.'.en')
        <p class="invalid-feedback d-block">{{ $message }}</p>
    @enderror
</div>
<div class="mb-3">
    <label for="{{ $field }}_ar" class="form-label">{{ $label }} ({{ __('messages.Arabic') }})</label>
    @if ($type === 'textarea')
        <textarea name="{{ $field }}[ar]" id="{{ $field }}_ar" rows="{{ $rows }}" dir="rtl" class="{{ $inputClassAr }}">{{ $arValue }}</textarea>
    @else
        <input type="text" name="{{ $field }}[ar]" id="{{ $field }}_ar" value="{{ $arValue }}" dir="rtl" class="{{ $inputClassAr }}">
    @endif
    @error($field.'.ar')
        <p class="invalid-feedback d-block">{{ $message }}</p>
    @enderror
</div>
