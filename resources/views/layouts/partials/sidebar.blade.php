<aside class="admin-sidebar" id="admin-sidebar">
    <div class="sidebar-content">
        <div class="sidebar-brand text-center">
            {{-- <a href="{{ route('v1.admin.dashboard') }}" class="d-inline-block">
                @include('layouts.partials.brand-logo', ['height' => 72])
            </a> --}}
        </div>
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                @can('view dashboard')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.dashboard') ? 'active' : '' }}"
                            href="{{ route('v1.admin.dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>{{ __('messages.Dashboard') }}</span>
                        </a>
                    </li>
                @endcan

                @canany(['manage categories', 'manage products', 'manage brands', 'manage branches', 'manage variants'])
                    <li class="nav-item mt-3">
                        <small class="text-muted px-3 text-uppercase fw-bold">{{ __('messages.Catalog') }}</small>
                    </li>
                @endcanany
                @can('manage categories')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.categories.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.categories.index') }}">
                            <i class="bi bi-grid"></i>
                            <span>{{ __('messages.Categories') }}</span>
                        </a>
                    </li>
                @endcan
                @can('manage products')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.products.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.products.index') }}">
                            <i class="bi bi-box-seam"></i>
                            <span>{{ __('messages.Products') }}</span>
                        </a>
                    </li>
                @endcan
                @can('manage brands')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.brands.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.brands.index') }}">
                            <i class="bi bi-award"></i>
                            <span>{{ __('messages.Brands') }}</span>
                        </a>
                    </li>
                @endcan
                @can('manage branches')
                    <li class="nav-item" style="display: none">
                        <a class="nav-link {{ request()->routeIs('v1.admin.branches.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.branches.index') }}">
                            <i class="bi bi-shop"></i>
                            <span>{{ __('messages.Branches') }}</span>
                        </a>
                    </li>
                @endcan
                @can('manage variants')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.variants.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.variants.index') }}">
                            <i class="bi bi-tags"></i>
                            <span>{{ __('messages.Variants') }}</span>
                        </a>
                    </li>
                @endcan

                @canany(['manage coupons', 'manage offers', 'manage goals', 'manage sliders'])
                    <li class="nav-item mt-3">
                        <small class="text-muted px-3 text-uppercase fw-bold">{{ __('messages.Marketing') }}</small>
                    </li>
                @endcanany
                @can('manage coupons')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.coupons.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.coupons.index') }}">
                            <i class="bi bi-ticket-perforated"></i>
                            <span>{{ __('messages.Coupons') }}</span>
                        </a>
                    </li>
                @endcan
                @can('manage offers')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.offers.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.offers.index') }}">
                            <i class="bi bi-gift"></i>
                            <span>{{ __('messages.Offers') }}</span>
                        </a>
                    </li>
                @endcan
                @can('manage goals')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.goals.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.goals.index') }}">
                            <i class="bi bi-bullseye"></i>
                            <span>{{ __('messages.Goals') }}</span>
                        </a>
                    </li>
                @endcan
                @can('manage sliders')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.sliders.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.sliders.index') }}">
                            <i class="bi bi-images"></i>
                            <span>{{ __('messages.Sliders') }}</span>
                        </a>
                    </li>
                @endcan

                @canany(['manage orders', 'manage refunds'])
                    <li class="nav-item mt-3">
                        <small class="text-muted px-3 text-uppercase fw-bold">{{ __('messages.Sales') }}</small>
                    </li>
                @endcanany
                @can('manage orders')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.orders.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.orders.index') }}">
                            <i class="bi bi-cart-check"></i>
                            <span>{{ __('messages.Orders') }}</span>
                        </a>
                    </li>
                @endcan
                @can('manage refunds')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.order-refund-requests.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.order-refund-requests.index') }}">
                            <i class="bi bi-arrow-return-left"></i>
                            <span>{{ __('messages.Order refund requests') }}</span>
                        </a>
                    </li>
                @endcan

                @can('view reports')
                    <li class="nav-item mt-3">
                        <small class="text-muted px-3 text-uppercase fw-bold">{{ __('messages.Analytics') }}</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.reports.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.reports.index') }}">
                            <i class="bi bi-graph-up"></i>
                            <span>{{ __('messages.Reports & Analytics') }}</span>
                        </a>
                    </li>
                @endcan

                @canany(['manage ratings', 'manage product-reports', 'manage tickets'])
                    <li class="nav-item mt-3">
                        <small class="text-muted px-3 text-uppercase fw-bold">{{ __('messages.Rating & Support') }}</small>
                    </li>
                @endcanany
                @can('manage ratings')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.product-ratings.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.product-ratings.index') }}">
                            <i class="bi bi-star"></i>
                            <span>{{ __('messages.Product ratings') }}</span>
                        </a>
                    </li>
                @endcan
                @can('manage product-reports')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.product-reports.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.product-reports.index') }}">
                            <i class="bi bi-flag"></i>
                            <span>{{ __('messages.Product reports') }}</span>
                        </a>
                    </li>
                @endcan
                @can('manage tickets')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.tickets.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.tickets.index') }}">
                            <i class="bi bi-chat-dots"></i>
                            <span>{{ __('messages.Tickets') }}</span>
                        </a>
                    </li>
                @endcan
                @canany(['manage admins', 'manage customers'])
                    <li class="nav-item mt-3">
                        <small
                            class="text-muted px-3 text-uppercase fw-bold">{{ __('messages.Admins and Customers') }}</small>
                    </li>
                @endcanany
                @can('manage admins')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.admin-users.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.admin-users.index') }}">
                            <i class="bi bi-people"></i>
                            <span>{{ __('messages.Admin Users') }}</span>
                        </a>
                    </li>
                @endcan
                @can('manage customers')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.customers.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.customers.index') }}">
                            <i class="bi bi-people"></i>
                            <span>{{ __('messages.Customer Users') }}</span>
                        </a>
                    </li>
                @endcan
                @can('manage settings')
                    <li class="nav-item mt-3">
                        <small class="text-muted px-3 text-uppercase fw-bold">{{ __('messages.System') }}</small>
                    </li>
                @endcan
                @can('manage settings')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.settings.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.settings.index') }}">
                            <i class="bi bi-gear"></i>
                            <span>{{ __('messages.Settings') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.security.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.security.index') }}">
                            <i class="bi bi-shield-lock"></i>
                            <span>{{ __('messages.Security') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('v1.admin.help.*') ? 'active' : '' }}"
                            href="{{ route('v1.admin.help.index') }}">
                            <i class="bi bi-question-circle"></i>
                            <span>{{ __('messages.Help') }}</span>
                        </a>
                    </li>
                @endcan

            </ul>
        </nav>
    </div>
</aside>
