@php
    $typeBadge = match ($type ?? '') {
        'recommendation' => 'success',
        'complaint' => 'danger',
        default => 'danger',
    };
    $typeBadgeText = $typeBadge === 'danger' ? 'text-white' : 'text-dark';
@endphp
<span class="badge bg-{{ $typeBadge }} {{ $typeBadgeText }}">
    {{ \App\Models\Ticket::typeLabel($type ?? 'complaint') }}
</span>
