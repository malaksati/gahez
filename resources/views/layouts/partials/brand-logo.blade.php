@php
    $height = $height ?? 44;
    $class = trim(($class ?? '').' brand-logo');
@endphp

<img
    src="{{ brand_logo_url() }}"
    alt="{{ setting('app_name') }}"
    height="{{ $height }}"
    class="{{ $class }}"
>
