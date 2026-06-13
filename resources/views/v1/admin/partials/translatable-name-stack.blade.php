@props([
    'model',
    'field' => 'name',
    'prefix' => null,
    'suffix' => null,
    'wrapperClass' => '',
])

@php
    $locale = app()->getLocale();
    $nameEn = (string) ($model->getTranslation($field, 'en', false) ?: '');
    $nameAr = (string) ($model->getTranslation($field, 'ar', false) ?: '');

    if ($locale === 'ar') {
        $primary = $nameAr !== '' ? $nameAr : $nameEn;
        $secondary = $nameEn;
        $primaryDir = 'rtl';
        $secondaryDir = 'ltr';
    } else {
        $primary = $nameEn !== '' ? $nameEn : $nameAr;
        $secondary = $nameAr;
        $primaryDir = 'ltr';
        $secondaryDir = 'rtl';
    }

    $showSecondary = $secondary !== '' && $secondary !== $primary;
@endphp

<div @class([$wrapperClass])>
    @if ($prefix)
        {!! $prefix !!}
    @endif
    <strong dir="{{ $primaryDir }}">{{ $primary !== '' ? $primary : '—' }}</strong>
    @if ($suffix)
        {!! $suffix !!}
    @endif
    @if ($showSecondary)
        <br>
        <small class="text-muted" dir="{{ $secondaryDir }}">{{ $secondary }}</small>
    @endif
</div>
