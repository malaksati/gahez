@extends('layouts.app')

@php
    $page = 'dashboard';
@endphp

@section('title', __('messages.Dashboard'))
@section('subtitle', __('messages.Welcome back, :name', ['name' => auth()->user()->name]))

@section('content')
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-5 g-3 g-xl-4 mb-4 dashboard-stats-row">
        @foreach ($stats as $stat)
            @php
                $iconClass = match ($stat['color']) {
                    'blue' => 'bg-primary bg-opacity-10 text-primary',
                    'emerald' => 'bg-success bg-opacity-10 text-success',
                    'violet' => 'bg-info bg-opacity-10 text-info',
                    'rose' => 'bg-danger bg-opacity-10 text-danger',
                    default => 'bg-warning bg-opacity-10 text-warning',
                };
                $icon = match ($stat['icon']) {
                    'cart' => 'bi-bag-check',
                    'cube' => 'bi-box-seam',
                    'users' => 'bi-people',
                    'chat' => 'bi-chat-dots',
                    default => 'bi-currency-dollar',
                };
            @endphp
            <div>
                <div class="card stats-card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="stats-icon {{ $iconClass }}">
                                    <i class="bi {{ $icon }}"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-muted">{{ $stat['label'] }}</h6>
                                <h3 class="mb-0">{{ $stat['value'] }}@if(!empty($stat['suffix'])) <small class="text-muted">{{ $stat['suffix'] }}</small>@endif</h3>
                                <small class="text-muted">{{ $stat['description'] }}</small>
                            </div>
                        </div>
                        @if(!empty($stat['href']))
                            <a href="{{ $stat['href'] }}" class="stretched-link" aria-label="{{ $stat['label'] }}"></a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($hasPendingActions)
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="d-flex align-items-start">
                <i class="bi bi-exclamation-triangle fs-4 me-3 flex-shrink-0"></i>
                <div class="flex-grow-1">
                    <strong>{{ __('messages.Action required') }}:</strong>
                    <div class="mt-2 d-flex flex-wrap gap-2">
                        @if ($canManageOrders && $pendingOrders > 0)
                            <a href="{{ route('v1.admin.orders.index', ['status' => 'pending']) }}" class="badge bg-warning text-dark text-decoration-none">
                                {{ $pendingOrders }} {{ __('messages.Pending orders') }}
                            </a>
                        @endif
                        @if ($canManageOrders && $processingOrders > 0)
                            <a href="{{ route('v1.admin.orders.index', ['status' => 'processing']) }}" class="badge bg-warning text-dark text-decoration-none">
                                {{ $processingOrders }} {{ __('messages.Processing orders') }}
                            </a>
                        @endif
                        @if ($canManageOrders && $ordersReadyForDelivery > 0)
                            <a href="{{ route('v1.admin.orders.index', ['status' => 'ready_for_delivery']) }}" class="badge bg-warning text-dark text-decoration-none">
                                {{ $ordersReadyForDelivery }} {{ __('messages.Orders ready for delivery') }}
                            </a>
                        @endif
                        @if ($canManageRefunds && $pendingRefundRequests > 0)
                            <a href="{{ route('v1.admin.order-refund-requests.index', ['status' => 'pending']) }}" class="badge bg-warning text-dark text-decoration-none">
                                {{ $pendingRefundRequests }} {{ __('messages.Refund requests') }}
                            </a>
                        @endif
                        @if ($canManageTickets && $openTickets > 0)
                            <a href="{{ route('v1.admin.tickets.index', ['status' => 'pending']) }}" class="badge bg-warning text-dark text-decoration-none">
                                {{ $openTickets }} {{ __('messages.Open tickets') }}
                            </a>
                        @endif
                        @if ($canManageProductReports && $pendingProductReports > 0)
                            <a href="{{ route('v1.admin.product-reports.index', ['status' => 'pending']) }}" class="badge bg-warning text-dark text-decoration-none">
                                {{ $pendingProductReports }} {{ __('messages.Product reports') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>{{ __('messages.Recent orders') }}
                    </h5>
                    <a href="{{ route('v1.admin.orders.index') }}" class="btn btn-sm btn-outline-primary">{{ __('messages.View all') }}</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('messages.Customer') }}</th>
                                    <th>{{ __('messages.Total') }}</th>
                                    <th>{{ __('messages.Status') }}</th>
                                    <th>{{ __('messages.Payment') }}</th>
                                    <th>{{ __('messages.Date') }}</th>
                                    <th class="text-end">{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentOrders as $order)
                                    <tr>
                                        <td><strong>#{{ $order->id }}</strong></td>
                                        <td>
                                            <div>{{ $order->user?->name ?? '?' }}</div>
                                            @if ($order->user?->email)
                                                <small class="text-muted">{{ $order->user->email }}</small>
                                            @endif
                                        </td>
                                        <td><strong>{{ number_format((float) $order->total, 2) }}</strong></td>
                                        <td>
                                            @include('v1.admin.orders.partials.order-status-badge', ['status' => $order->status])
                                        </td>
                                        <td>
                                            @include('v1.admin.orders.partials.order-payment-status-badge', ['paymentStatus' => $order->payment_status])
                                        </td>
                                        <td class="small text-muted">{{ $order->created_at?->format('M j, Y') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('v1.admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary" title="{{ __('messages.View') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="bi bi-cart-x display-6 d-block mb-2"></i>
                                            {{ __('messages.No orders yet.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>{{ __('messages.Quick actions') }}
                    </h5>
                </div>
                <div class="card-body dashboard-quick-actions">
                    <div class="d-grid gap-2">
                        @if ($canManageOrders)
                            <a href="{{ route('v1.admin.orders.index', ['status' => 'pending']) }}" class="btn btn-outline-warning d-flex align-items-center gap-2">
                                <i class="bi bi-clock-history flex-shrink-0"></i>
                                <span class="flex-grow-1">{{ __('messages.Pending orders') }}</span>
                                @if ($pendingOrders > 0)
                                    <span class="badge bg-warning text-dark">{{ $pendingOrders }}</span>
                                @endif
                            </a>
                            <a href="{{ route('v1.admin.orders.index', ['status' => 'processing']) }}" class="btn btn-outline-info d-flex align-items-center gap-2">
                                <i class="bi bi-gear flex-shrink-0"></i>
                                <span class="flex-grow-1">{{ __('messages.Processing orders') }}</span>
                                @if ($processingOrders > 0)
                                    <span class="badge bg-info">{{ $processingOrders }}</span>
                                @endif
                            </a>
                            <a href="{{ route('v1.admin.orders.index', ['status' => 'ready_for_delivery']) }}" class="btn btn-outline-primary d-flex align-items-center gap-2">
                                <i class="bi bi-truck flex-shrink-0"></i>
                                <span class="flex-grow-1">{{ __('messages.Orders ready for delivery') }}</span>
                                @if ($ordersReadyForDelivery > 0)
                                    <span class="badge bg-primary">{{ $ordersReadyForDelivery }}</span>
                                @endif
                            </a>
                            <a href="{{ route('v1.admin.orders.create') }}" class="btn btn-outline-success d-flex align-items-center gap-2">
                                <i class="bi bi-cart-plus flex-shrink-0"></i>
                                <span class="flex-grow-1">{{ __('messages.New Order') }}</span>
                            </a>
                        @endif
                        @if ($canManageRefunds)
                            <a href="{{ route('v1.admin.order-refund-requests.index') }}" class="btn btn-outline-danger d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-return-left flex-shrink-0"></i>
                                <span class="flex-grow-1">{{ __('messages.Refund requests') }}</span>
                                @if ($pendingRefundRequests > 0)
                                    <span class="badge bg-danger">{{ $pendingRefundRequests }}</span>
                                @endif
                            </a>
                        @endif
                        @if ($canManageTickets)
                            <a href="{{ route('v1.admin.tickets.index', ['status' => 'pending']) }}" class="btn btn-outline-primary d-flex align-items-center gap-2">
                                <i class="bi bi-chat-dots flex-shrink-0"></i>
                                <span class="flex-grow-1">{{ __('messages.Open tickets') }}</span>
                                @if ($openTickets > 0)
                                    <span class="badge bg-primary">{{ $openTickets }}</span>
                                @endif
                            </a>
                        @endif
                        @if ($canManageProductReports)
                            <a href="{{ route('v1.admin.product-reports.index', ['status' => 'pending']) }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                                <i class="bi bi-flag flex-shrink-0"></i>
                                <span class="flex-grow-1">{{ __('messages.Product reports') }}</span>
                                @if ($pendingProductReports > 0)
                                    <span class="badge bg-secondary">{{ $pendingProductReports }}</span>
                                @endif
                            </a>
                        @endif
                        @can('manage products')
                            <a href="{{ route('v1.admin.products.create') }}" class="btn btn-outline-success d-flex align-items-center gap-2">
                                <i class="bi bi-plus-lg flex-shrink-0"></i>
                                <span class="flex-grow-1">{{ __('messages.New product') }}</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
