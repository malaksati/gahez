@extends('layouts.auth')

@section('title', __('messages.Sign up'))
@section('branding-title', __('messages.Sign up'))
@section('branding-description', __('messages.Create a new account to get started'))
@section('form-title', __('messages.Sign up'))
@section('form-subtitle', __('messages.Create a new account to get started'))

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="mb-3">
        <label for="name" class="form-label">{{ __('messages.Name') }}</label>
        <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" id="name"
            name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">{{ __('messages.Email') }}</label>
        <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" id="email"
            name="email" value="{{ old('email') }}" autocomplete="email">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="phone" class="form-label">{{ __('messages.Phone') }}</label>
        <input type="text" class="form-control form-control-lg @error('phone') is-invalid @enderror" id="phone"
            name="phone" value="{{ old('phone') }}" autocomplete="tel">
        <small class="text-muted d-block mt-1">{{ __('messages.Provide email or phone') }}</small>
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">{{ __('messages.Password') }}</label>
        <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
            id="password" name="password" required autocomplete="new-password">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label">{{ __('messages.Confirm password') }}</label>
        <input type="password" class="form-control form-control-lg" id="password_confirmation"
            name="password_confirmation" required autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-primary btn-lg w-100">{{ __('messages.Create account') }}</button>
</form>
@endsection

@section('footer')
    <div class="text-center text-muted">
        {{ __('messages.Already have an account?') }}
        <a href="{{ route('login') }}" class="fw-semibold">{{ __('messages.Sign in') }}</a>
    </div>
@endsection
