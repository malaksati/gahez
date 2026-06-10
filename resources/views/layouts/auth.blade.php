<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light" id="auth-html" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ __('messages.Sign in') }} — {{ setting('app_name') }}">
    <meta name="author" content="{{ setting('app_name') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ brand_logo_url() }}">

    <title>@yield('title', __('messages.Sign in')) — {{ setting('app_name') }}</title>
    <meta name="theme-color" content="{{ brand_color('700') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="auth-page">
    <div class="auth-container">
        <div class="auth-wrapper">
            <div class="auth-branding d-none d-lg-flex">
                <div class="auth-branding-content">
                    <a href="{{ route('home') }}" class="auth-logo mb-4 d-block text-center">
                        @include('layouts.partials.brand-logo', ['height' => 120])
                    </a>
                    <h2 class="h4 text-white mb-3">@yield('branding-title', __('messages.Sign in'))</h2>
                    <p class="text-white-50 mb-0">@yield('branding-description', __('messages.Enter your credentials to access your account'))</p>
                </div>
            </div>

            <div class="auth-form-wrapper">
                <div class="auth-form-container">
                    <div class="text-center mb-4 d-lg-none">
                        <a href="{{ route('home') }}" class="d-inline-block">
                            @include('layouts.partials.brand-logo', ['height' => 56])
                        </a>
                    </div>

                    <div class="auth-card">
                        <div class="auth-card-header">
                            <h3 class="auth-card-title">@yield('form-title', __('messages.Sign in'))</h3>
                            <p class="auth-card-subtitle text-muted">@yield('form-subtitle', __('messages.Enter your credentials to access your account'))</p>
                        </div>

                        <div class="auth-card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                                </div>
                            @endif

                            @yield('content')
                        </div>

                        <div class="auth-card-footer">
                            @yield('footer')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const savedTheme = localStorage.getItem('theme') || 'light';
            const html = document.getElementById('auth-html');
            if (html) {
                html.setAttribute('data-bs-theme', savedTheme);
            }
        });
    </script>
</body>
</html>
