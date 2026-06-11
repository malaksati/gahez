@extends('layouts.app')

@php
    $page = 'reports';
    $currency = display_currency();
    $isRtl = app()->getLocale() === 'ar';
@endphp

@section('title', __('messages.Reports & Analytics'))
@section('subtitle', __('messages.Reports hub description'))

@push('styles')
    <style>
        .reports-sidebar {
            --reports-sidebar-title-size: 1rem;
            --reports-sidebar-desc-size: 0.8rem;
            --reports-sidebar-icon-size: 1.125rem;
        }
    </style>
@endpush

@section('content')
    <div class="reports-page">
    <div class="row g-3 align-items-start">
        <div class="col-lg-9">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h2 class="h5 mb-1">{{ __('messages.Analytics overview') }}</h2>
                    <p class="text-muted small mb-0">{{ __('messages.Analytics overview hint') }}</p>
                </div>
                <span class="badge text-bg-light">{{ $charts['period_label'] }}</span>
            </div>

            <div class="row g-3">
                <div class="col-md-8">
                    <div class="card gahez-chart-card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-graph-up-arrow me-2 text-primary"></i>{{ __('messages.Revenue trend') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="revenueTrendChart" class="gahez-chart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card gahez-chart-card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-credit-card me-2 text-info"></i>{{ __('messages.Sales by payment method') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="paymentMethodsChart" class="gahez-chart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card gahez-chart-card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-bag-check me-2 text-success"></i>{{ __('messages.Orders trend') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="ordersTrendChart" class="gahez-chart gahez-chart--sm"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card gahez-chart-card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-trophy me-2 text-warning"></i>{{ __('messages.Top products') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="topProductsChart" class="gahez-chart gahez-chart--sm"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card border-0 shadow-sm reports-sidebar sticky-lg-top" style="top: 1rem;">
                <div class="card-header bg-white border-bottom">
                    <h2 class="h6 mb-0">{{ __('messages.All reports') }}</h2>
                </div>
                <div class="card-body p-2">
                    <div class="reports-sidebar-links">
                        @foreach ($reports as $item)
                            <a
                                href="{{ route('v1.admin.reports.show', $item['key']) }}"
                                class="reports-sidebar-link"
                            >
                                <span class="reports-sidebar-link__icon" aria-hidden="true">
                                    <i class="bi {{ $item['icon'] }}"></i>
                                </span>
                                <span class="reports-sidebar-link__content">
                                    <span class="reports-sidebar-link__title">{{ $item['title'] }}</span>
                                    <span class="reports-sidebar-link__desc">{{ $item['description'] }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
    @include('v1.admin.partials.analytics-charts-script', [
        'charts' => $charts,
        'currency' => $currency,
        'isRtl' => $isRtl,
        'rootSelector' => '.reports-page',
        'ordersEnhancedGrid' => true,
    ])
@endpush
