@props(['model'])

@if ($model->start_date || $model->end_date)
    <div class="d-flex flex-wrap align-items-center gap-1">
        @if ($model->start_date)
            <span class="badge bg-info">
                <i class="bi bi-calendar-event me-1"></i>{{ $model->start_date->format('M d, Y') }}
            </span>
        @endif
        @if ($model->start_date && $model->end_date)
            <i class="bi bi-arrow-right text-muted"></i>
        @endif
        @if ($model->end_date)
            <span class="badge bg-primary">
                <i class="bi bi-calendar-check me-1"></i>{{ $model->end_date->format('M d, Y') }}
            </span>
        @endif
    </div>
@else
    <span class="badge bg-secondary">{{ __('messages.Unlimited') }}</span>
@endif
