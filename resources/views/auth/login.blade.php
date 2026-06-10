@extends('layouts.auth')

@section('title', __('messages.Sign in'))
@section('branding-title', __('messages.Sign in'))
@section('branding-description', __('messages.Enter your credentials to access your account'))
@section('form-title', __('messages.Sign in'))
@section('form-subtitle', __('messages.Enter your credentials to access your account'))

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
        <label for="login" class="form-label">{{ __('messages.Email or phone') }}</label>
        <input type="text" name="login" id="login" value="{{ old('login') }}" class="form-control form-control-lg @error('login') is-invalid @enderror" required autofocus autocomplete="username">
        @error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label mb-0">{{ __('messages.Password') }}</label>
            <a href="{{ route('password.request') }}" class="small">{{ __('messages.Forgot password?') }}</a>
        </div>
        <input type="password" name="password" id="password" class="form-control form-control-lg @error('password') is-invalid @enderror" required autocomplete="current-password">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-4 form-check">
        <input type="checkbox" class="form-check-input" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
        <label class="form-check-label" for="remember">{{ __('messages.Remember me') }}</label>
    </div>

    <button type="submit" class="btn btn-primary btn-lg w-100">{{ __('messages.Sign in') }}</button>
</form>
@endsection

@section('footer')
    {{ __('messages.Dont have an account?') }}
    <a href="{{ route('register') }}" class="fw-semibold">{{ __('messages.Sign up') }}</a>
@endsection
