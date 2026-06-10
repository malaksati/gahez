<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h6 class="mb-0">{{ __('messages.Basic Information') }}</h6>
            </div>
            <div class="card-body py-4">
                <div class="mb-3">
                    <label for="name" class="form-label required">{{ __('messages.Name') }}</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $customer->name ?? '') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="birthdate" class="form-label">{{ __('messages.Birthdate') }}</label>
                    <input
                        type="date"
                        class="form-control @error('birthdate') is-invalid @enderror"
                        id="birthdate"
                        name="birthdate"
                        value="{{ old('birthdate', isset($customer) && $customer->birthdate ? $customer->birthdate->format('Y-m-d') : '') }}"
                        max="{{ now()->subDay()->toDateString() }}"
                    >
                    <small class="text-muted">{{ __('messages.Birthdate hint') }}</small>
                    @error('birthdate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">{{ __('messages.Email') }}</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $customer->email ?? '') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">{{ __('messages.Phone') }}</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $customer->phone ?? '') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label {{ !isset($customer) ? 'required' : '' }}">
                            {{ __('messages.Password') }}
                            @if(isset($customer))
                                <small class="text-muted fw-normal ms-1">({{ __('messages.leave blank to keep current') }})</small>
                            @endif
                        </label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" {{ !isset($customer) ? 'required' : '' }}>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label {{ !isset($customer) ? 'required' : '' }}">{{ __('messages.Confirm Password') }}</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" {{ !isset($customer) ? 'required' : '' }}>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h6 class="mb-0">{{ __('messages.Status') }}</h6>
            </div>
            <div class="card-body py-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" 
                        {{ old('is_active', $customer->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">{{ __('messages.Active Account') }}</label>
                </div>
                <div class="form-text mt-2">
                    {{ __('messages.Inactive customers will not be able to log in.') }}
                </div>
            </div>
        </div>
    </div>
</div>
