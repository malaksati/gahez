<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ setting('app_name') }} — {{ __('messages.Admin') }}">
    <meta name="author" content="{{ setting('app_name') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ brand_logo_url() }}">

    <title>@yield('title', __('messages.Dashboard')) - {{ setting('app_name') }}</title>
    <meta name="theme-color" content="{{ brand_color('700') }}">
    <link rel="manifest" href="{{ asset('dashboard/manifest.json') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">

    <script>
        window.__adminSearchUrl = @json(route('v1.admin.search'));
        window.__adminSearchLabels = {
            placeholder: @json(__('messages.Search admin…')),
            noResults: @json(__('messages.No search results')),
            minChars: @json(__('messages.Type at least 2 characters to search')),
        };
        @auth
        window.__adminNotifications = {
            feedUrl: @json(route('v1.admin.notifications.feed')),
            markReadUrl: @json(route('v1.admin.notifications.read', ['notification' => '__ID__'])),
            labels: {
                notification: @json(__('messages.Notification')),
                newNotification: @json(__('messages.New notification')),
                noNotifications: @json(__('messages.No notifications')),
                close: @json(__('messages.Close')),
            },
        };
        @endauth
        window.__addressMapPickerLabels = {
            empty: @json(__('messages.No location selected')),
            selected: @json(__('messages.Location coordinates')),
            modalHint: @json(__('messages.Map picker modal hint')),
        };
    </script>
    @stack('head-config')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body data-page="{{ $page ?? 'dashboard' }}" class="admin-layout {{ app()->getLocale() === 'ar' ? 'ar' : 'en' }}">
    <div id="loading-screen" class="loading-screen">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ __('messages.Loading...') }}</span>
            </div>
        </div>
    </div>

    <div class="admin-wrapper" id="admin-wrapper">
        @include('layouts.partials.header')
        @include('layouts.partials.sidebar')

        <main class="admin-main">
            <div class="container-fluid p-4 p-lg-5">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('messages.Close') }}"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('messages.Close') }}"></button>
                    </div>
                @endif

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

                @hasSection('page-header')
                    @yield('page-header')
                @else
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div>
                            <h1 class="h3 mb-0">@yield('title', __('messages.Dashboard'))</h1>
                            @hasSection('subtitle')
                                <p class="text-muted mb-0">@yield('subtitle')</p>
                            @endif
                        </div>
                        <div class="d-flex gap-2">@yield('page-actions')</div>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

        @include('layouts.partials.footer')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleButton = document.querySelector('[data-sidebar-toggle]');
            const wrapper = document.getElementById('admin-wrapper');

            if (! toggleButton || ! wrapper) {
                return;
            }

            if (localStorage.getItem('sidebar-collapsed') === 'true') {
                wrapper.classList.add('sidebar-collapsed');
                toggleButton.classList.add('is-active');
            }

            toggleButton.addEventListener('click', () => {
                const collapsed = wrapper.classList.toggle('sidebar-collapsed');
                toggleButton.classList.toggle('is-active', collapsed);
                localStorage.setItem('sidebar-collapsed', collapsed ? 'true' : 'false');
            });
        });
    </script>

    <div id="admin-toast-container" class="admin-toast-container" aria-live="polite" aria-atomic="true"></div>

    @include('v1.admin.partials.confirm-action-modal')
    @stack('modals')
    @stack('scripts')
</body>
</html>
