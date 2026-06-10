@php
    $p = (string) ($paymentStatus ?? '');
    $class = match ($p) {
        'pending' => 'bg-warning text-dark',
        'paid' => 'bg-success',
        'failed' => 'bg-danger',
        'refunded' => 'bg-secondary',
        default => 'bg-secondary',
    };
    $label = $p !== '' ? __('messages.'.$p) : '—';
@endphp
<span class="badge {{ $class }} text-capitalize">{{ $label }}</span>
