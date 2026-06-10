@csrf

<div class="card-body">
    <div>
        <label for="slider-image" class="form-label">{{ __('messages.Image') }}</label>
        <input id="slider-image" type="file" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
        @error('image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if (isset($slider) && $slider->image)
            <div class="mt-3">
                <div class="small text-muted mb-1">{{ __('messages.Image') }}</div>
                <img src="{{ $slider->image_path }}" alt="{{ __('messages.Image') }}" class="img-thumbnail" style="max-height: 140px;">
            </div>
        @endif
    </div>
</div>

<div class="card-footer bg-white d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ __('messages.Save') }}</button>
    <a href="{{ route('v1.admin.sliders.index') }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a>
</div>
