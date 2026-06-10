@extends('layouts.app')

@php
    $page = 'reports';
    $currency = app_currency();
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
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">{{ __('messages.Revenue trend') }}</h5>
                        </div>
                        <div class="card-body">
                            <div id="revenueTrendChart" style="min-height: 280px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">{{ __('messages.Sales by payment method') }}</h5>
                        </div>
                        <div class="card-body">
                            <div id="paymentMethodsChart" style="min-height: 280px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">{{ __('messages.Orders trend') }}</h5>
                        </div>
                        <div class="card-body">
                            <div id="ordersTrendChart" style="min-height: 260px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">{{ __('messages.Top products') }}</h5>
                        </div>
                        <div class="card-body">
                            <div id="topProductsChart" style="min-height: 260px;"></div>
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof ApexCharts === 'undefined') {
                return;
            }

            const isRtl = @json($isRtl);
            const currency = @json($currency);
            const charts = @json($charts);

            const baseChartOptions = {
                chart: {
                    fontFamily: 'Inter, Noto Sans Arabic, sans-serif',
                    toolbar: { show: false },
                    zoom: { enabled: false },
                },
                dataLabels: { enabled: false },
                grid: {
                    borderColor: '#e5e7eb',
                    strokeDashArray: 4,
                },
                tooltip: {
                    theme: 'light',
                },
            };

            const lineOptions = (color, yPrefix = '') => ({
                ...baseChartOptions,
                chart: {
                    ...baseChartOptions.chart,
                    type: 'line',
                    height: 280,
                },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                },
                colors: [color],
                xaxis: {
                    categories: charts.revenue_trend.labels,
                    labels: {
                        rotate: isRtl ? 45 : -45,
                    },
                },
                yaxis: {
                    labels: {
                        formatter: (value) => yPrefix + Number(value).toLocaleString(),
                    },
                },
            });

            if (document.getElementById('revenueTrendChart')) {
                new ApexCharts(document.getElementById('revenueTrendChart'), {
                    ...lineOptions('#2563eb'),
                    series: [{
                        name: @json(__('messages.Revenue')),
                        data: charts.revenue_trend.values,
                    }],
                    yaxis: {
                        labels: {
                            formatter: (value) => Number(value).toLocaleString() + ' ' + currency,
                        },
                    },
                }).render();
            }

            if (document.getElementById('ordersTrendChart')) {
                new ApexCharts(document.getElementById('ordersTrendChart'), {
                    ...lineOptions('#059669'),
                    chart: {
                        ...baseChartOptions.chart,
                        type: 'line',
                        height: 260,
                    },
                    series: [{
                        name: @json(__('messages.Orders')),
                        data: charts.orders_trend.values,
                    }],
                    xaxis: {
                        categories: charts.orders_trend.labels,
                        labels: {
                            rotate: isRtl ? 45 : -45,
                        },
                    },
                }).render();
            }

            if (document.getElementById('paymentMethodsChart')) {
                new ApexCharts(document.getElementById('paymentMethodsChart'), {
                    ...baseChartOptions,
                    chart: {
                        ...baseChartOptions.chart,
                        type: 'donut',
                        height: 280,
                    },
                    series: charts.payment_methods.values,
                    labels: charts.payment_methods.labels,
                    colors: ['#2563eb', '#059669', '#d97706', '#dc2626', '#7c3aed', '#64748b'],
                    legend: {
                        position: 'bottom',
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '62%',
                            },
                        },
                    },
                }).render();
            }

            if (document.getElementById('topProductsChart')) {
                new ApexCharts(document.getElementById('topProductsChart'), {
                    ...baseChartOptions,
                    chart: {
                        ...baseChartOptions.chart,
                        type: 'bar',
                        height: 260,
                    },
                    series: [{
                        name: @json(__('messages.Quantity sold')),
                        data: charts.top_products.values,
                    }],
                    colors: ['#1e3a5f'],
                    plotOptions: {
                        bar: {
                            borderRadius: 6,
                            horizontal: true,
                        },
                    },
                    xaxis: {
                        categories: charts.top_products.labels,
                    },
                }).render();
            }
        });
    </script>
@endpush
