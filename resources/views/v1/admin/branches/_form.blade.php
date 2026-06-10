@csrf
@include('v1.admin.partials.translatable-inputs', ['field' => 'name', 'model' => $branch ?? null])
<div class="mt-4">
    <label for="address" class="form-label">{{ __('messages.Address') }}</label>
    <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror" required>{{ old('address', $branch->address ?? '') }}</textarea>
    @error('address')
        <p class="invalid-feedback d-block">{{ $message }}</p>
    @enderror
</div>
<div class="mt-4">
    <label for="phone" class="form-label">{{ __('messages.Phone') }}</label>
    <input type="text" name="phone" id="phone" value="{{ old('phone', $branch->phone ?? '') }}" class="form-control @error('phone') is-invalid @enderror">
    @error('phone')
        <p class="invalid-feedback d-block">{{ $message }}</p>
    @enderror
</div>
<div class="row mt-4 g-3">
    <div class="col-md-6">
        <label for="latitude" class="form-label">{{ __('messages.Latitude') }}</label>
        <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $branch->latitude ?? '') }}"
            class="form-control @error('latitude') is-invalid @enderror" placeholder="24.7136" required>
        @error('latitude')
            <p class="invalid-feedback d-block">{{ $message }}</p>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="longitude" class="form-label">{{ __('messages.Longitude') }}</label>
        <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $branch->longitude ?? '') }}"
            class="form-control @error('longitude') is-invalid @enderror" placeholder="46.6753" required>
        @error('longitude')
            <p class="invalid-feedback d-block">{{ $message }}</p>
        @enderror
    </div>
</div>
<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ __('messages.Save') }}</button>
    <a href="{{ route('v1.admin.branches.index') }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a>
</div>
