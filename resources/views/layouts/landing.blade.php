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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="landing-page {{ app()->getLocale() === 'ar' ? 'ar' : 'en' }}">
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top shadow-sm">
    <div class="container py-2">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
            @include('layouts.partials.brand-logo', ['height' => 44])
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#landingNav" aria-controls="landingNav" aria-expanded="false" aria-label="{{ __('messages.Menu') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="landingNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active fw-semibold' : '' }}" href="{{ route('home') }}">{{ __('messages.Home') }}</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-translate me-1"></i>{{ strtoupper(app()->getLocale()) }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}" href="{{ route('locale.switch', ['locale' => 'en']) }}">English</a></li>
                        <li><a class="dropdown-item {{ app()->getLocale() === 'ar' ? 'active' : '' }}" href="{{ route('locale.switch', ['locale' => 'ar']) }}">العربية</a></li>
                    </ul>
                </li>

                @auth
                    @if (auth()->user()->hasRole('admin'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('v1.admin.dashboard') }}">
                                <i class="bi bi-speedometer2 me-1"></i>{{ __('messages.Dashboard') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('v1.admin.profile.edit') }}">
                                <i class="bi bi-person me-1"></i>{{ __('messages.Profile') }}
                            </a>
                        </li>
                    @endif
                    <li class="nav-item ms-lg-1">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-box-arrow-right me-1"></i>{{ __('messages.Sign out') }}
                            </button>
                        </form>
                    </li>
                @else
                    <li class="nav-item ms-lg-1">
                        <a class="btn btn-outline-primary" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i>{{ __('messages.Sign in') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary" href="{{ route('register') }}">
                            <i class="bi bi-person-plus me-1"></i>{{ __('messages.Sign up') }}
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main>
    @yield('content')
</main>

<footer class="border-top bg-white">
    <div class="container py-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center gap-3">
            <div class="text-muted text-center text-lg-start">
                &copy; {{ now()->year }} {{ setting('app_name') }}. {{ __('messages.All rights reserved.') }}
            </div>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                @auth
                    @if (auth()->user()->hasRole('admin'))
                        <a class="text-decoration-none" href="{{ route('v1.admin.dashboard') }}">{{ __('messages.Dashboard') }}</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link text-danger p-0 text-decoration-none">{{ __('messages.Sign out') }}</button>
                    </form>
                @else
                    <a class="text-decoration-none" href="{{ route('login') }}">{{ __('messages.Sign in') }}</a>
                    <a class="text-decoration-none" href="{{ route('register') }}">{{ __('messages.Sign up') }}</a>
                    {{-- <a class="text-decoration-none" href="{{ route('become-delivery.create') }}">{{ __('Become a delivery man') }}</a> --}}

                @endauth
            </div>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
