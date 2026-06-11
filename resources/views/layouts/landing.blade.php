<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ __('messages.Home hero subtitle') }}">

    <link rel="icon" href="{{ brand_logo_url() }}">

    <title>@yield('title', __('messages.Home')) — {{ setting('app_name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="landing-page {{ app()->getLocale() === 'ar' ? 'ar' : 'en' }}">
<header class="landing-nav" id="landingNavBar">
    <div class="container">
        <nav class="navbar navbar-expand-lg py-0">
            <a class="navbar-brand d-flex align-items-center py-0" href="{{ route('home') }}">
                @include('layouts.partials.brand-logo', ['height' => 40])
            </a>

            <button class="navbar-toggler landing-nav-toggler border" type="button" data-bs-toggle="collapse"
                data-bs-target="#landingNav" aria-controls="landingNav" aria-expanded="false"
                aria-label="{{ __('messages.Menu') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="landingNav">
                <ul class="navbar-nav {{ app()->getLocale() === 'ar' ? 'me-auto' : 'ms-auto' }} align-items-lg-center gap-lg-1">
                    <li class="nav-item">
                        <a class="landing-nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                            href="{{ route('home') }}">{{ __('messages.Home') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="landing-nav-link" href="#features">{{ __('messages.Why choose us') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="landing-nav-link" href="#how-it-works">{{ __('messages.How it works') }}</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="landing-nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bi bi-translate me-1"></i>{{ strtoupper(app()->getLocale()) }}
                        </a>
                        <ul class="dropdown-menu {{ app()->getLocale() === 'ar' ? 'dropdown-menu-start' : 'dropdown-menu-end' }}">
                            <li>
                                <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}"
                                    href="{{ route('locale.switch', ['locale' => 'en']) }}">English</a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ app()->getLocale() === 'ar' ? 'active' : '' }}"
                                    href="{{ route('locale.switch', ['locale' => 'ar']) }}">العربية</a>
                            </li>
                        </ul>
                    </li>

                    @auth
                        @if (auth()->user()->hasRole('admin'))
                            <li class="nav-item">
                                <a class="landing-nav-link" href="{{ route('v1.admin.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i>{{ __('messages.Dashboard') }}
                                </a>
                            </li>
                        @endif
                        <li class="nav-item ms-lg-2">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                    <i class="bi bi-box-arrow-right me-1"></i>{{ __('messages.Sign out') }}
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-sm btn-landing-outline" href="{{ route('login') }}">
                                {{ __('messages.Sign in') }}
                            </a>
                        </li>
                        <li class="nav-item ms-lg-1">
                            <a class="btn btn-sm btn-landing-primary" href="{{ route('register') }}">
                                {{ __('messages.Sign up') }}
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </nav>
    </div>
</header>

<main>
    @yield('content')
</main>

<footer class="landing-footer">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <div class="footer-brand mb-2">{{ setting('app_name') }}</div>
                <p class="small mb-0 footer-desc">{{ __('messages.Home hero subtitle') }}</p>
            </div>
            <div class="col-6 col-lg-2">
                <div class="small footer-heading mb-3">{{ __('messages.Explore') }}</div>
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                    <li><a href="{{ route('home') }}">{{ __('messages.Home') }}</a></li>
                    <li><a href="#features">{{ __('messages.Why choose us') }}</a></li>
                    <li><a href="#how-it-works">{{ __('messages.How it works') }}</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <div class="small footer-heading mb-3">{{ __('messages.Account') }}</div>
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                    @auth
                        @if (auth()->user()->hasRole('admin'))
                            <li><a href="{{ route('v1.admin.dashboard') }}">{{ __('messages.Dashboard') }}</a></li>
                            <li><a href="{{ route('v1.admin.profile.edit') }}">{{ __('messages.Profile') }}</a></li>
                        @endif
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 text-start">{{ __('messages.Sign out') }}</button>
                            </form>
                        </li>
                    @else
                        <li><a href="{{ route('login') }}">{{ __('messages.Sign in') }}</a></li>
                        <li><a href="{{ route('register') }}">{{ __('messages.Sign up') }}</a></li>
                    @endauth
                </ul>
            </div>
        </div>
        <div class="footer-copy d-flex flex-column flex-lg-row justify-content-between align-items-center gap-2 text-center text-lg-start">
            <span>&copy; {{ now()->year }} {{ setting('app_name') }}. {{ __('messages.All rights reserved.') }}</span>
        </div>
    </div>
</footer>

@stack('scripts')
<script>
    (function () {
        const nav = document.getElementById('landingNavBar');
        if (!nav) return;

        const onScroll = () => {
            nav.classList.toggle('is-scrolled', window.scrollY > 24);
        };

        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    })();
</script>
</body>
</html>
