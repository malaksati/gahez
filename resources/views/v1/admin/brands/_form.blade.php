@csrf
@include('v1.admin.partials.translatable-inputs', ['model' => $brand ?? null])
<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ __('messages.Save') }}</button>
    <a href="{{ route('v1.admin.brands.index') }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a>
</div>
