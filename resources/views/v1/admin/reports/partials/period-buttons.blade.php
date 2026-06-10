@php
    use Carbon\Carbon;

    $currentPeriod = $filters['period_type'] ?? 'monthly';
    $periodPresets = [
        'daily' => __('messages.Today'),
        'weekly' => __('messages.Last 7 days'),
        'monthly' => __('messages.Last 30 days'),
        'manual' => __('messages.Manual range'),
    ];
    $formatDate = fn (?string $date) => $date ? Carbon::parse($date)->format('d-m-Y') : null;
    $resolvedFrom = $filters['resolved_from'] ?? null;
    $resolvedTo = $filters['resolved_to'] ?? null;
@endphp

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <span class="text-muted small">{{ __('messages.Period') }}:</span>
    <div class="btn-group flex-wrap" role="group" aria-label="{{ __('messages.Period type') }}">
        @foreach ($periodPresets as $key => $label)
            @php
                $query = array_filter(array_merge(
                    request()->except(['period_type', 'from_date', 'to_date', 'page']),
                    ['period_type' => $key]
                ), fn ($value) => $value !== null && $value !== '');
            @endphp
            <a
                href="{{ route('v1.admin.reports.show', $type) }}{{ $query ? '?' . http_build_query($query) : '' }}"
                class="btn btn-sm {{ $currentPeriod === $key ? 'btn-primary' : 'btn-outline-primary' }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>
    @if ($resolvedFrom && $resolvedTo)
        <span class="badge text-bg-light">
            {{ $formatDate($resolvedFrom) }} — {{ $formatDate($resolvedTo) }}
        </span>
    @endif
</div>
