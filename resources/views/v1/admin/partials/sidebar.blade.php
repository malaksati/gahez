@php
    $navGroups = [
        [
            'label' => null,
            'items' => [
                [
                    'route' => 'v1.admin.dashboard',
                    'pattern' => 'v1.admin.dashboard',
                    'label' => __('messages.Dashboard'),
                    'icon' => 'home',
                ],
            ],
        ],
        [
            'label' => __('messages.Catalog'),
            'items' => [
                ['route' => 'v1.admin.categories.index', 'pattern' => 'v1.admin.categories.*', 'label' => __('messages.Categories'), 'icon' => 'folder'],
                ['route' => 'v1.admin.products.index', 'pattern' => 'v1.admin.products.*', 'label' => __('messages.Products'), 'icon' => 'cube'],
                ['route' => 'v1.admin.brands.index', 'pattern' => 'v1.admin.brands.*', 'label' => __('messages.Brands'), 'icon' => 'tag'],
                ['route' => 'v1.admin.branches.index', 'pattern' => 'v1.admin.branches.*', 'label' => __('messages.Branches'), 'icon' => 'building'],
                ['route' => 'v1.admin.variants.index', 'pattern' => 'v1.admin.variants.*', 'label' => __('messages.Variants'), 'icon' => 'swatch'],
            ],
        ],
        [
            'label' => __('messages.Marketing'),
            'items' => [
                ['route' => 'v1.admin.coupons.index', 'pattern' => 'v1.admin.coupons.*', 'label' => __('messages.Coupons'), 'icon' => 'ticket'],
                ['route' => 'v1.admin.offers.index', 'pattern' => 'v1.admin.offers.*', 'label' => __('messages.Offers'), 'icon' => 'gift'],
                ['route' => 'v1.admin.sliders.index', 'pattern' => 'v1.admin.sliders.*', 'label' => __('messages.Sliders'), 'icon' => 'photo'],
            ],
        ],
        [
            'label' => __('messages.Sales'),
            'items' => [
                ['route' => 'v1.admin.orders.index', 'pattern' => 'v1.admin.orders.*', 'label' => __('messages.Orders'), 'icon' => 'cart'],
                ['route' => 'v1.admin.order-refund-requests.index', 'pattern' => 'v1.admin.order-refund-requests.*', 'label' => __('messages.Order refund requests'), 'icon' => 'refund'],
            ],
        ],
        [
            'label' => __('messages.Analytics'),
            'items' => [
                ['route' => 'v1.admin.reports.index', 'pattern' => 'v1.admin.reports.index', 'label' => __('messages.Reports & Analytics'), 'icon' => 'chart'],
                ['route' => 'v1.admin.reports.earnings', 'pattern' => 'v1.admin.reports.earnings', 'label' => __('messages.Earnings'), 'icon' => 'currency'],
                ['route' => 'v1.admin.reports.product-performance', 'pattern' => 'v1.admin.reports.product-performance', 'label' => __('messages.Product performance'), 'icon' => 'cube'],
            ],
        ],
        [
            'label' => __('messages.Support'),
            'items' => [
                ['route' => 'v1.admin.tickets.index', 'pattern' => 'v1.admin.tickets.*', 'label' => __('messages.Tickets'), 'icon' => 'chat'],
            ],
        ],
    ];
@endphp

<aside
    id="admin-sidebar"
    class="fixed inset-y-0 start-0 z-50 flex w-64 flex-col bg-gray-950 text-white transition-transform duration-200 lg:translate-x-0 {{ app()->getLocale() === 'ar' ? 'translate-x-full' : '-translate-x-full' }}"
>
    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
        <div class="flex size-9 items-center justify-center rounded-lg bg-amber-500 text-sm font-bold text-gray-950">
            {{ strtoupper(substr(config('app.name'), 0, 1)) }}
        </div>
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold">{{ config('app.name') }}</p>
            <p class="truncate text-xs text-gray-400">{{ __('messages.Admin') }}</p>
        </div>
        <button type="button" id="admin-sidebar-close" class="rounded-lg p-1.5 text-gray-400 hover:bg-white/10 hover:text-white lg:hidden" aria-label="{{ __('messages.Close') }}">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4">
        @foreach ($navGroups as $group)
            @if ($group['label'])
                <p class="mb-2 mt-4 px-3 text-xs font-semibold uppercase tracking-wider text-gray-500 first:mt-0">
                    {{ $group['label'] }}
                </p>
            @endif
            <ul class="space-y-0.5">
                @foreach ($group['items'] as $item)
                    @php
                        $active = request()->routeIs($item['pattern']);
                    @endphp
                    <li>
                        <a
                            href="{{ route($item['route']) }}"
                            @class([
                                'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                                'bg-white/10 text-white' => $active,
                                'text-gray-300 hover:bg-white/5 hover:text-white' => ! $active,
                            ])
                        >
                            @include('v1.admin.partials.nav-icon', ['name' => $item['icon']])
                            <span class="truncate">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </nav>

    <div class="border-t border-white/10 p-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-300 transition hover:bg-white/5 hover:text-white">
                @include('v1.admin.partials.nav-icon', ['name' => 'logout'])
                {{ __('messages.Sign out') }}
            </button>
        </form>
    </div>
</aside>
