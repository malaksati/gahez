@props(['model'])

@switch ($model->validityStatus())
    @case('running')
        <br><small class="text-success">{{ __('messages.Running') }}</small>
        @break
    @case('scheduled')
        <br><small class="text-warning">{{ __('messages.Scheduled') }}</small>
        @break
    @case('expired')
        <br><small class="text-danger">{{ __('messages.Expired') }}</small>
        @break
@endswitch
