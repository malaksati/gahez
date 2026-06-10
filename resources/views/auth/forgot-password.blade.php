@extends('layouts.auth')

@section('title', __('messages.Forgot password'))
@section('branding-title', __('messages.Forgot password'))
@section('branding-description', __('messages.We will email you a reset link'))
@section('form-title', __('messages.Forgot password'))
@section('form-subtitle', __('messages.We will email you a reset link'))

@section('content')
<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="mb-4">
        <label for="email" class="form-label">{{ __('messages.Email') }}</label>
        <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" id="email"
            name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn btn-primary btn-lg w-100">{{ __('messages.Send reset link') }}</button>
</form>
@endsection

@section('footer')
    <a href="{{ route('login') }}" class="fw-semibold">{{ __('messages.Back to sign in') }}</a>
@endsection
