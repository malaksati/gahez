@extends('layouts.auth')

@section('title', __('messages.Confirm password'))
@section('branding-title', __('messages.Confirm password'))
@section('branding-description', __('messages.Please confirm your password to continue'))
@section('form-title', __('messages.Confirm password'))
@section('form-subtitle', __('messages.Please confirm your password to continue'))

@section('content')
<form method="POST" action="{{ route('password.confirm') }}">
    @csrf

    <div class="mb-4">
        <label for="password" class="form-label">{{ __('messages.Password') }}</label>
        <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
            id="password" name="password" required autofocus autocomplete="current-password">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn btn-primary btn-lg w-100">{{ __('messages.Confirm') }}</button>
</form>
@endsection
