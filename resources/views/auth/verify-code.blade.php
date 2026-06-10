@extends('layouts.auth')

@section('title', __('messages.Verify code'))
@section('branding-title', __('messages.Verify code'))
@section('branding-description', __('messages.Enter the verification code we sent you'))
@section('form-title', __('messages.Verify code'))
@section('form-subtitle', __('messages.Enter the verification code we sent you'))

@section('content')
<form method="POST" action="{{ route('auth.verification.submit') }}">
    @csrf

    <div class="mb-3">
        <label for="code" class="form-label">{{ __('messages.Verification code') }}</label>
        <input type="text" name="code" id="code" inputmode="numeric" maxlength="6" required autofocus
            class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
            style="letter-spacing: 0.35em; font-size: 1.25rem;">
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    @if (session('email'))
        <input type="hidden" name="email" value="{{ session('email') }}">
        <p class="text-center text-muted small mb-3">
            {{ __('messages.We sent a code to') }} <strong>{{ session('email') }}</strong>
        </p>
    @elseif (session('phone'))
        <input type="hidden" name="phone" value="{{ session('phone') }}">
        <p class="text-center text-muted small mb-3">
            {{ __('messages.We sent a code to') }} <strong>{{ session('phone') }}</strong>
        </p>
    @else
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('messages.Email') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control">
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">{{ __('messages.Phone') }}</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="form-control">
        </div>
    @endif

    <button type="submit" class="btn btn-primary btn-lg w-100">{{ __('messages.Verify') }}</button>
</form>

<form method="POST" action="{{ route('auth.verification.resend') }}" class="mt-3 text-center">
    @csrf
    @if (session('email'))
        <input type="hidden" name="email" value="{{ session('email') }}">
    @endif
    @if (session('phone'))
        <input type="hidden" name="phone" value="{{ session('phone') }}">
    @endif
    <button type="submit" class="btn btn-link">{{ __('messages.Resend code') }}</button>
</form>
@endsection
