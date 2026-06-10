@extends('layouts.app')

@section('title', __('messages.Security'))
@section('subtitle', __('messages.Security overview'))

@section('content')
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6"><i class="bi bi-shield-check text-primary me-2"></i>{{ __('messages.Account security') }}</h2>
                    <p class="text-muted small mb-0">{{ __('messages.Security account hint') }}</p>
                    <a href="{{ route('v1.admin.profile.edit') }}" class="btn btn-sm btn-outline-primary mt-3">{{ __('messages.Edit profile') }}</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6"><i class="bi bi-key text-primary me-2"></i>{{ __('messages.Password') }}</h2>
                    <p class="text-muted small mb-0">{{ __('messages.Security password hint') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
