@auth
    <div class="admin-nav-offcanvas-footer">
        <div class="admin-nav-offcanvas-account">
            <div class="d-flex align-items-center gap-2 px-1 mb-2">
                <img
                    src="{{ auth()->user()->image }}"
                    alt=""
                    width="36"
                    height="36"
                    class="rounded-circle flex-shrink-0"
                    style="object-fit: cover;"
                >
                <span class="fw-medium small text-truncate">{{ auth()->user()->name }}</span>
            </div>

            <nav class="nav flex-row flex-wrap gap-2 admin-nav-offcanvas-account-links">
                <a class="nav-link py-2 px-3" href="{{ route('v1.admin.profile.edit') }}">
                    <i class="bi bi-person"></i>
                    <span>{{ __('messages.Profile') }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="nav-link py-2 px-3 border-0 bg-transparent">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>{{ __('messages.Sign out') }}</span>
                    </button>
                </form>
            </nav>
        </div>

        <div class="admin-nav-offcanvas-toggles">
            @include('layouts.partials.theme-switch-button', ['variant' => 'offcanvas'])
            @include('layouts.partials.locale-switch-button', ['variant' => 'offcanvas'])
        </div>
    </div>
@endauth
