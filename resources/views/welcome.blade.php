@extends('layouts.landing')

@section('title', __('Home'))

@section('content')
    @if (session('info'))
        <div class="container position-fixed top-0 start-50 translate-middle-x mt-5 pt-5" style="z-index: 1050; max-width: 540px;">
            <div class="alert alert-info alert-dismissible fade show shadow" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    {{-- Hero --}}
    <section class="landing-hero">
        <div class="landing-hero-grid"></div>
        <div class="container position-relative" style="z-index: 1;">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="landing-hero-badge mb-4">
                        <i class="bi bi-stars"></i>
                        {{ __('Home hero badge') }}
                    </div>
                    <h1 class="landing-hero-title">
                        {{ __('Home hero title') }}
                        <span class="highlight d-block">{{ setting('app_name') }}</span>
                    </h1>
                    <p class="landing-hero-subtitle mb-4">{{ __('Home hero subtitle') }}</p>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-light-hero btn-lg">
                                <i class="bi bi-bag-heart me-2"></i>{{ __('Get started') }}
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-ghost-hero btn-lg">
                                {{ __('Sign in') }}
                            </a>
                        @else
                            <p class="landing-hero-muted mb-2 w-100 small">
                                <i class="bi bi-person-check me-1"></i>
                                {{ __('Welcome back, :name', ['name' => auth()->user()->name]) }}
                            </p>
                            @if (auth()->user()->hasRole('admin'))
                                <a href="{{ route('v1.admin.dashboard') }}" class="btn btn-light-hero btn-lg">
                                    <i class="bi bi-speedometer2 me-2"></i>{{ __('Go to dashboard') }}
                                </a>
                                <a href="{{ route('v1.admin.profile.edit') }}" class="btn btn-ghost-hero btn-lg">
                                    <i class="bi bi-person me-2"></i>{{ __('Profile') }}
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-ghost-hero btn-lg">
                                    <i class="bi bi-box-arrow-right me-2"></i>{{ __('Sign out') }}
                                </button>
                            </form>
                        @endguest
                    </div>

                    <div class="d-flex flex-wrap gap-4 landing-hero-muted small">
                        <span><i class="bi bi-check-circle-fill text-warning me-1"></i>{{ __('Home feature quality title') }}</span>
                        <span><i class="bi bi-check-circle-fill text-warning me-1"></i>{{ __('Home feature delivery title') }}</span>
                        <span><i class="bi bi-check-circle-fill text-warning me-1"></i>{{ __('Home feature secure title') }}</span>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="landing-hero-visual">
                        <div class="landing-hero-float landing-hero-float--top">
                            <i class="bi bi-truck me-1"></i>{{ __('Home feature delivery title') }}
                        </div>
                        <div class="landing-hero-card text-center">
                            @include('layouts.partials.brand-logo', ['height' => 180, 'class' => 'mx-auto'])
                            <p class="mt-3 mb-0 small landing-hero-muted">{{ __('Home hero badge') }}</p>
                        </div>
                        <div class="landing-hero-float landing-hero-float--bottom">
                            <i class="bi bi-shield-check me-1"></i>{{ __('Home feature secure title') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats strip --}}
    <section class="landing-stats">
        <div class="container">
            <div class="row g-3">
                <div class="col-4">
                    <div class="landing-stat">
                        <div class="value"><i class="bi bi-award text-primary"></i></div>
                        <div class="label">{{ __('Home feature quality title') }}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="landing-stat">
                        <div class="value"><i class="bi bi-truck text-primary"></i></div>
                        <div class="label">{{ __('Home feature delivery title') }}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="landing-stat">
                        <div class="value"><i class="bi bi-shield-check text-primary"></i></div>
                        <div class="label">{{ __('Home feature secure title') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features bento --}}
    <section class="landing-section bg-white" id="features">
        <div class="container">
            <div class="landing-section-header text-center mx-auto">
                <h2>{{ __('Why choose us') }}</h2>
                <p>{{ __('Why choose us subtitle') }}</p>
            </div>

            <div class="landing-bento">
                <div class="landing-bento-item landing-bento-item--wide landing-bento-item--accent">
                    <div class="landing-bento-icon">
                        <i class="bi bi-award"></i>
                    </div>
                    <h3>{{ __('Home feature quality title') }}</h3>
                    <p>{{ __('Home feature quality text') }}</p>
                </div>
                <div class="landing-bento-item">
                    <div class="landing-bento-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h3>{{ __('Home feature delivery title') }}</h3>
                    <p>{{ __('Home feature delivery text') }}</p>
                </div>
                <div class="landing-bento-item">
                    <div class="landing-bento-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3>{{ __('Home feature secure title') }}</h3>
                    <p>{{ __('Home feature secure text') }}</p>
                </div>
                <div class="landing-bento-item landing-bento-item--wide">
                    <div class="landing-bento-icon">
                        <i class="bi bi-phone"></i>
                    </div>
                    <h3>{{ __('Home feature mobile title') }}</h3>
                    <p>{{ __('Home feature mobile text') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section class="landing-section" id="how-it-works" style="background: #faf6ee;">
        <div class="container">
            <div class="landing-section-header text-center mx-auto">
                <h2>{{ __('How it works') }}</h2>
                <p>{{ __('How it works subtitle') }}</p>
            </div>

            <div class="landing-steps">
                <div class="landing-step">
                    <div class="step-num">01</div>
                    <h3>{{ __('Home step browse title') }}</h3>
                    <p>{{ __('Home step browse text') }}</p>
                </div>
                <div class="landing-step">
                    <div class="step-num">02</div>
                    <h3>{{ __('Home step order title') }}</h3>
                    <p>{{ __('Home step order text') }}</p>
                </div>
                <div class="landing-step">
                    <div class="step-num">03</div>
                    <h3>{{ __('Home step enjoy title') }}</h3>
                    <p>{{ __('Home step enjoy text') }}</p>
                </div>
            </div>
        </div>
    </section>

    @guest
        <section class="landing-cta">
            <div class="container">
                <div class="row align-items-center justify-content-between g-4">
                    <div class="col-lg-7">
                        <h2>{{ __('Ready to shop') }}</h2>
                        <p class="mb-0">{{ __('Ready to shop subtitle') }}</p>
                    </div>
                    <div class="col-lg-auto d-flex flex-wrap gap-2">
                        <a href="{{ route('register') }}" class="btn btn-light-hero btn-lg">
                            {{ __('Create account') }}
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-ghost-hero btn-lg">
                            {{ __('Sign in') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endguest
@endsection
