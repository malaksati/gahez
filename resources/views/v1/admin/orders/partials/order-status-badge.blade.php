@php
    $s = (string) ($status ?? '');
    $class = match ($s) {
        'pending' => 'bg-warning text-dark',
        'processing' => 'bg-info',
        'shipped' => 'bg-primary',
        'delivered' => 'bg-success',
        'refunded' => 'bg-secondary',
        'cancelled' => 'bg-danger',
        default => 'bg-secondary',
    };
    $label = $s !== '' ? __('messages.'.$s) : '—';
@endphp
<span class="badge {{ $class }} text-capitalize">{{ $label }}</span>
