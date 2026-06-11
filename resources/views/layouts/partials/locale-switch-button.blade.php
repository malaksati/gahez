@props(['variant' => 'admin'])

@php
    $currentLocale = app()->getLocale();
    $isArabic = str_starts_with($currentLocale, 'ar');
    $enUrl = route('locale.switch', ['locale' => 'en']);
    $arUrl = route('locale.switch', ['locale' => 'ar']);
@endphp

<button
    type="button"
    class="pill-toggle pill-toggle--locale pill-toggle--{{ $variant }}"
    x-data="localeSwitch({ current: '{{ $currentLocale }}', enUrl: '{{ $enUrl }}', arUrl: '{{ $arUrl }}' })"
    :class="{
        'is-en': !isArabic,
        'is-ar': isArabic,
        'is-right': isArabic,
    }"
    @click="toggle()"
    aria-label="{{ __('messages.Switch language') }}"
>
    <span class="pill-toggle__label pill-toggle__label--start" x-show="isArabic" x-cloak>AR</span>
    <span class="pill-toggle__label pill-toggle__label--end" x-show="!isArabic">EN</span>
    <span class="pill-toggle__thumb">
        <span x-show="!isArabic">
            @include('layouts.partials.flags.uk-circle')
        </span>
        <span x-show="isArabic" x-cloak>
            @include('layouts.partials.flags.egypt-circle')
        </span>
    </span>
</button>
