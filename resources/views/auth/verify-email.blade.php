@extends('layouts.auth')

@section('title', __('messages.Verify email'))
@section('branding-title', __('messages.Verify email'))
@section('branding-description', __('messages.Click the link we sent or request a new one'))
@section('form-title', __('messages.Verify email'))
@section('form-subtitle', __('messages.Click the link we sent or request a new one'))

@section('content')
<form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <button type="submit" class="btn btn-primary btn-lg w-100">{{ __('messages.Resend verification email') }}</button>
</form>
@endsection
