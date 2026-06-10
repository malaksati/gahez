@props([
    'label',
    'value' => null,
    'col' => 'col-md-6',
    'dir' => null,
])

<div class="{{ $col }}">
    <small class="text-muted d-block">{{ $label }}</small>
    <p class="mb-0 fw-semibold" @if ($dir) dir="{{ $dir }}" @endif>
        {!! $value ?? ($slot ?? '—') !!}
    </p>
</div>
