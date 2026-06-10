@php
    $variantModel = $variant ?? null;
    $formOptions = [];

    if (old('options')) {
        $formOptions = old('options');
    } elseif ($variantModel) {
        foreach ($variantModel->options as $index => $option) {
            $formOptions[] = [
                'id' => $option->id,
                'name' => [
                    'en' => $option->getTranslation('name', 'en', false),
                    'ar' => $option->getTranslation('name', 'ar', false),
                ],
                'code' => $option->code,
            ];
        }
    }
@endphp

@csrf

@include('v1.admin.partials.translatable-inputs', ['model' => $variantModel])

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <input type="hidden" name="is_active" value="0">
        <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="variant_is_active" name="is_active" value="1"
                @checked(old('is_active', $variantModel?->is_active ?? true))>
            <label class="form-check-label" for="variant_is_active">{{ __('messages.Active') }}</label>
        </div>
        <small class="text-muted">{{ __('messages.Active variants will be available for products') }}</small>
    </div>
    <div class="col-md-6">
        <input type="hidden" name="is_required" value="0">
        <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="variant_is_required" name="is_required" value="1"
                @checked(old('is_required', $variantModel?->is_required ?? false))>
            <label class="form-check-label" for="variant_is_required">{{ __('messages.Required') }}</label>
        </div>
        <small class="text-muted">{{ __('messages.Required variants must be selected when creating products') }}</small>
    </div>
</div>

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <label class="form-label mb-0 fw-semibold">{{ __('messages.Variant options') }}</label>
            <small class="text-muted d-block">{{ __('messages.Define variant options inline hint') }}</small>
        </div>
        <button type="button" class="btn btn-sm btn-primary" id="addVariantOptionBtn">
            <i class="bi bi-plus-lg me-1"></i>{{ __('messages.Add option') }}
        </button>
    </div>

    <div id="variantOptionsContainer">
        @foreach ($formOptions as $index => $option)
            @include('v1.admin.variants.partials.option-row', [
                'index' => $index,
                'option' => $option,
            ])
        @endforeach
    </div>
</div>

@error('options')
    <div class="invalid-feedback d-block mb-3">{{ $message }}</div>
@enderror
@error('options.*')
    <div class="invalid-feedback d-block mb-3">{{ $message }}</div>
@enderror

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg me-1"></i>{{ __('messages.Save') }}
    </button>
    <a href="{{ route('v1.admin.variants.index') }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a>
</div>

@push('scripts')
    <script>
        window.__variantFormOptions = {
            initialCount: {{ count($formOptions) }},
            optionLabel: @json(__('messages.Option')),
            nameEnLabel: @json(__('messages.Name') . ' (' . __('messages.English') . ')'),
            nameArLabel: @json(__('messages.Name') . ' (' . __('messages.Arabic') . ')'),
            codeLabel: @json(__('messages.Code')),
            autoLabel: @json(__('messages.Auto')),
            optionalLabel: @json(__('messages.Optional')),
        };
    </script>
@endpush
