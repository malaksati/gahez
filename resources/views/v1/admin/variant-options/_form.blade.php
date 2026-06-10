@csrf
@include('v1.admin.partials.translatable-inputs', ['model' => $variantOption ?? null])
<div class="mt-4">
    <label class="form-label">{{ __('messages.Variant') }}</label>
    <select name="variant_id" class="w-full rounded-lg border px-3 py-2 text-sm">
        @foreach ($variants as $variant)
            <option value="{{ $variant->id }}" @selected(old('variant_id', $variantOption->variant_id ?? null) == $variant->id)>{{ $variant->getTranslation('name', 'en') }}</option>
        @endforeach
    </select>
</div>
<div class="mt-4">
    <label class="form-label">{{ __('messages.Code') }}</label>
    <input type="text" name="code" value="{{ old('code', $variantOption->code ?? '') }}" class="w-full rounded-lg border px-3 py-2 text-sm">
</div>
<div class="mt-4 d-flex gap-2"><button type="submit" class="btn btn-primary">{{ __('messages.Save') }}</button><a href="{{ route('v1.admin.variant-options.index') }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a></div>
