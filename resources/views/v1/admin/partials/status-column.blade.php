@props(['model'])

@include('v1.admin.partials.active-badge', ['active' => (bool) $model->is_active])

@if (method_exists($model, 'validityStatus') && ($model->start_date || $model->end_date))
    @include('v1.admin.partials.validity-schedule-status', ['model' => $model])
@endif
