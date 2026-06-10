@extends('layouts.auth')

@section('title', __('messages.Reset password'))
@section('branding-title', __('messages.Reset password'))
@section('branding-description', __('messages.Choose a new password'))
@section('form-title', __('messages.Reset password'))
@section('form-subtitle', __('messages.Choose a new password'))

@section('content')
<form method="POST" action="{{ route('password.store') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div class="mb-3">
        <label for="email" class="form-label">{{ __('messages.Email') }}</label>
        <input type="email" class="form-control form-control-lg" id="email" name="email"
            value="{{ old('email', $request->email) }}" required readonly>
        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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

    <button type="submit" class="btn btn-primary btn-lg w-100">{{ __('messages.Reset password') }}</button>
</form>
@endsection
