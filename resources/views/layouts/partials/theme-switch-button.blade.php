@props(['variant' => 'admin'])

<button
    type="button"
    class="pill-toggle pill-toggle--theme pill-toggle--{{ $variant }}"
    x-data="themeSwitch"
    :class="{
        'is-dark': currentTheme === 'dark',
        'is-light': currentTheme === 'light',
        'is-right': currentTheme === 'dark'
    }"
    @click="toggle()"
    title="{{ __('messages.Toggle theme') }}"
    aria-label="{{ __('messages.Toggle theme') }}"
>
    <span class="pill-toggle__label pill-toggle__label--end" x-show="currentTheme === 'light'">{{ __('messages.Light') }}</span>
    <span class="pill-toggle__label pill-toggle__label--start" x-show="currentTheme === 'dark'" x-cloak>{{ __('messages.Dark') }}</span>

    <span class="pill-toggle__thumb">
        <i class="bi bi-sun-fill" x-show="currentTheme === 'light'"></i>
        <i class="bi bi-moon-stars-fill" x-show="currentTheme === 'dark'" x-cloak></i>
    </span>
</button>
