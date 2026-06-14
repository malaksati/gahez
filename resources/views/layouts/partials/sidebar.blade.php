<aside class="admin-sidebar" id="admin-sidebar">
    <div class="sidebar-content">
        <div class="sidebar-brand text-center"></div>
        @include('layouts.partials.sidebar-nav')
    </div>
</aside>

<div
    class="offcanvas offcanvas-start admin-nav-offcanvas"
    tabindex="-1"
    id="adminNavOffcanvas"
    aria-labelledby="adminNavOffcanvasLabel"
>
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="adminNavOffcanvasLabel">{{ __('messages.Menu') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('messages.Close') }}"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
        <div class="admin-nav-offcanvas-scroll flex-grow-1">
            @include('layouts.partials.sidebar-nav')
        </div>
        @include('layouts.partials.offcanvas-menu-footer')
    </div>
</div>
