@csrf

@php
    $isEdit = isset($slider);
@endphp

<div class="card-body">
    <div class="mb-3">
        <label for="slider-type" class="form-label">{{ __('messages.Slider type') }}</label>
        <select
            id="slider-type"
            name="type"
            class="form-select @error('type') is-invalid @enderror"
            required
        >
            @foreach (\App\Models\Slider::typeLabels() as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('type', $slider->type ?? \App\Models\Slider::TYPE_HOME) === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">{{ __('messages.Slider type hint') }}</div>
    </div>

    @include('v1.admin.sliders.partials.image-upload', [
        'existingUrl' => $isEdit ? $slider->image_path : null,
        'required' => ! $isEdit,
    ])
</div>

<div class="card-footer bg-white d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ __('messages.Save') }}</button>
    <a href="{{ route('v1.admin.sliders.index') }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a>
</div>
