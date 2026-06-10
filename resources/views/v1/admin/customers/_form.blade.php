@php
    $isEdit = isset($customer);
    $primaryAddress = $isEdit
        ? ($customer->addresses->firstWhere('is_default') ?? $customer->addresses->first())
        : null;
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h6 class="mb-0">{{ __('messages.Basic Information') }}</h6>
            </div>
            <div class="card-body py-4">
                @if ($isEdit)
                    <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
                        <img src="{{ $customer->image }}" alt="{{ $customer->name }}" class="rounded-circle"
                            width="72" height="72" style="object-fit: cover;">
                        <div>
                            <div class="fw-semibold">{{ $customer->name }}</div>
                            <div class="text-muted small">{{ $customer->email ?: $customer->phone }}</div>
                            @if ($customer->referral_code)
                                <div class="text-muted small mt-1">
                                    {{ __('messages.Referral code') }}:
                                    <code>{{ $customer->referral_code }}</code>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label required">{{ __('messages.Name') }}</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                        value="{{ old('name', $isEdit ? $customer->name : '') }}" required>
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
                        value="{{ old('birthdate', $isEdit && $customer->birthdate ? $customer->birthdate->format('Y-m-d') : '') }}"
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
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email', $isEdit ? $customer->email : '') }}" autocomplete="email">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">{{ __('messages.Phone') }}</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                            name="phone" value="{{ old('phone', $isEdit ? $customer->phone : '') }}" autocomplete="tel">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <p class="form-text mb-3">{{ __('messages.Provide email or phone') }}</p>

                <div class="mb-3">
                    <label for="image" class="form-label">{{ __('messages.Profile photo') }}</label>
                    <input type="file" name="image" id="image" accept="image/*"
                        class="form-control @error('image') is-invalid @enderror">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if ($isEdit && $customer->getRawOriginal('image'))
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" name="remove_image" id="remove_image" value="1"
                                {{ old('remove_image') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remove_image">{{ __('messages.Remove photo') }}</label>
                        </div>
                    @endif
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="wallet" class="form-label">{{ __('messages.Wallet balance') }}</label>
                        <input type="number" step="0.01" min="0" name="wallet" id="wallet"
                            class="form-control @error('wallet') is-invalid @enderror"
                            value="{{ old('wallet', $isEdit ? $customer->wallet : 0) }}">
                        @error('wallet')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="points" class="form-label">{{ __('messages.Points balance') }}</label>
                        <input type="number" step="1" min="0" name="points" id="points"
                            class="form-control @error('points') is-invalid @enderror"
                            value="{{ old('points', $isEdit ? $customer->points : 0) }}">
                        @error('points')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <p class="form-text mb-0">{{ __('messages.Customer balance adjustment hint') }}</p>

                <hr class="my-4">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label {{ ! $isEdit ? 'required' : '' }}">
                            {{ __('messages.Password') }}
                            @if ($isEdit)
                                <small class="text-muted fw-normal ms-1">({{ __('messages.leave blank to keep current') }})</small>
                            @endif
                        </label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                            name="password" {{ ! $isEdit ? 'required' : '' }} autocomplete="new-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label {{ ! $isEdit ? 'required' : '' }}">
                            {{ __('messages.Confirm Password') }}
                        </label>
                        <input type="password" class="form-control" id="password_confirmation"
                            name="password_confirmation" {{ ! $isEdit ? 'required' : '' }} autocomplete="new-password">
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h6 class="mb-0">{{ __('messages.Address') }}</h6>
            </div>
            <div class="card-body py-4">
                @include('v1.admin.customers.partials.address-fields', ['primaryAddress' => $primaryAddress])
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h6 class="mb-0">{{ __('messages.Status') }}</h6>
            </div>
            <div class="card-body py-4">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                        {{ old('is_active', $isEdit ? $customer->is_active : true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">{{ __('messages.Active Account') }}</label>
                </div>
                <div class="form-text mb-3">
                    {{ __('messages.Inactive customers will not be able to log in.') }}
                </div>

                <input type="hidden" name="is_verified" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_verified" name="is_verified" value="1"
                        {{ old('is_verified', $isEdit ? $customer->is_verified : true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_verified">{{ __('messages.Verified account') }}</label>
                </div>
                <div class="form-text mt-2">
                    {{ __('messages.Verified account hint') }}
                </div>
            </div>
        </div>
    </div>
</div>
