@php
    $typeBadge = match ($type ?? '') {
        'recommendation' => 'info',
        default => 'warning',
    };
@endphp
<span class="badge bg-{{ $typeBadge }} text-dark">
    {{ \App\Models\Ticket::typeLabel($type ?? 'complaint') }}
</span>
