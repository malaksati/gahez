@props(['model'])

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h5 class="card-title mb-0">{{ __('messages.Timestamps') }}</h5>
    </div>
    <div class="card-body">
        <small class="text-muted d-block">{{ __('messages.Created at') }}</small>
        <p class="mb-3">
            {{ $model->created_at?->format('M d, Y H:i') }}
            <span class="text-muted">({{ $model->created_at?->diffForHumans() }})</span>
        </p>
        <small class="text-muted d-block">{{ __('messages.Updated at') }}</small>
        <p class="mb-0">
            {{ $model->updated_at?->format('M d, Y H:i') }}
            <span class="text-muted">({{ $model->updated_at?->diffForHumans() }})</span>
        </p>
    </div>
</div>
