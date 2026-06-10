@extends('layouts.landing')

@section('title', __('Home'))

@section('content')
    @if (session('info'))
        <div class="container mt-3">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif
    <section class="py-5 border-bottom"
        style="background: linear-gradient(135deg, var(--gahez-50, #fef6e7) 0%, var(--gahez-100, #fde4b6) 100%);">
        <div class="container py-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 order-lg-1">
                    <span class="badge rounded-pill text-bg-primary mb-3">{{ __('Home hero badge') }}</span>
                    <h1 class="display-5 fw-bold mb-3 text-primary">{{ __('Home hero title') }}</h1>
                    <p class="lead text-muted mb-4">{{ __('Home hero subtitle') }}</p>
                    <div class="d-flex flex-wrap gap-2">
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-bag-heart me-2"></i>{{ __('Get started') }}
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>{{ __('Sign in') }}
                            </a>
                        @else
                            <p class="text-muted mb-2 w-100">
                                <i class="bi bi-person-check me-1"></i>
                                {{ __('Welcome back, :name', ['name' => auth()->user()->name]) }}
                            </p>
                            @if (auth()->user()->hasRole('admin'))
                                <a href="{{ route('v1.admin.dashboard') }}" class="btn btn-primary btn-lg">
                                    <i class="bi bi-speedometer2 me-2"></i>{{ __('Go to dashboard') }}
                                </a>
                                <a href="{{ route('v1.admin.profile.edit') }}" class="btn btn-outline-primary btn-lg">
                                    <i class="bi bi-person me-2"></i>{{ __('Profile') }}
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-lg">
                                    <i class="bi bi-box-arrow-right me-2"></i>{{ __('Sign out') }}
                                </button>
                            </form>
                        @endguest
                    </div>
                </div>
                <div class="col-lg-6 text-center order-lg-2">
                    @include('layouts.partials.brand-logo', ['height' => 220, 'class' => 'mx-auto'])
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="h3 fw-bold">{{ __('Why choose us') }}</h2>
                <p class="text-muted mb-0">{{ __('Why choose us subtitle') }}</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3"
                                style="width: 56px; height: 56px;">
                                <i class="bi bi-award fs-4"></i>
                            </div>
                            <h5 class="card-title">{{ __('Home feature quality title') }}</h5>
                            <p class="card-text text-muted mb-0">{{ __('Home feature quality text') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3"
                                style="width: 56px; height: 56px;">
                                <i class="bi bi-truck fs-4"></i>
                            </div>
                            <h5 class="card-title">{{ __('Home feature delivery title') }}</h5>
                            <p class="card-text text-muted mb-0">{{ __('Home feature delivery text') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3"
                                style="width: 56px; height: 56px;">
                                <i class="bi bi-shield-check fs-4"></i>
                            </div>
                            <h5 class="card-title">{{ __('Home feature secure title') }}</h5>
                            <p class="card-text text-muted mb-0">{{ __('Home feature secure text') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @guest
        <section class="py-5" style="background-color: var(--gahez-50, #fef6e7);">
            <div class="container text-center py-3">
                <h2 class="h4 fw-bold mb-3">{{ __('Ready to shop') }}</h2>
                <p class="text-muted mb-4">{{ __('Ready to shop subtitle') }}</p>
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg me-2">{{ __('Create account') }}</a>
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg">{{ __('Sign in') }}</a>
            </div>
        </section>
    @endguest
@endsection
