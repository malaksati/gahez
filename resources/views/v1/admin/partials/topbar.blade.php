<header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-gray-200 bg-white/80 px-4 backdrop-blur-sm sm:px-6 lg:px-8">
    <button
        type="button"
        id="admin-sidebar-open"
        class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 lg:hidden"
        aria-label="{{ __('messages.Menu') }}"
    >
        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
        </svg>
    </button>

    <div class="min-w-0 flex-1">
        <h1 class="truncate text-lg font-semibold text-gray-950">
            @yield('heading', __('messages.Dashboard'))
        </h1>
    </div>

    <div class="flex items-center gap-3">
        <div class="hidden text-end sm:block">
            <p class="text-sm font-medium text-gray-950">{{ auth()->user()->name }}</p>
            <p class="text-xs text-gray-500">{{ auth()->user()->email ?? auth()->user()->phone }}</p>
        </div>
        <div class="flex size-9 items-center justify-center rounded-full bg-amber-500/15 text-sm font-semibold text-amber-700">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
    </div>
</header>
