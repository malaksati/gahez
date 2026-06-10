@extends('layouts.app')

@php
    $page = 'reports';
    $currency = app_currency();
    $paidTotal = $report['kpis']['paid_orders_total'] ?? 0;
    $totalCommission = $report['kpis']['total_commission'] ?? 0;
    $refundedTotal = $report['kpis']['refunded_total'] ?? 0;
    $netRevenue = max(0, $paidTotal - $refundedTotal);
@endphp

@section('title', __('messages.Earnings dashboard'))
@section('subtitle', __('messages.Store earnings overview'))

@section('page-actions')
    <a href="{{ route('v1.admin.reports.index') }}" class="btn btn-outline-secondary btn-sm me-2">{{ __('messages.Reports overview') }}</a>
    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
        <i class="bi bi-sliders me-1"></i>{{ __('messages.Filters') }}
    </button>
@endsection

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">{{ __('messages.Paid revenue') }}</div>
                        <div class="fs-4 fw-semibold">{{ number_format($paidTotal, 2) }} {{ $currency }}</div>
                        <small class="text-muted">{{ __('messages.Paid orders') }}: {{ $report['kpis']['paid_orders_count'] ?? 0 }}</small>
                    </div>
                    <i class="bi bi-cash-stack text-success fs-4"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">{{ __('messages.Platform commission') }}</div>
                        <div class="fs-4 fw-semibold">{{ number_format($totalCommission, 2) }} {{ $currency }}</div>
                    </div>
                    <i class="bi bi-percent text-warning fs-4"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">{{ __('messages.Net revenue (after refunds)') }}</div>
                        <div class="fs-4 fw-semibold">{{ number_format($netRevenue, 2) }} {{ $currency }}</div>
                        <small class="text-muted">{{ __('messages.Refunded total') }}: {{ number_format($refundedTotal, 2) }} {{ $currency }}</small>
                    </div>
                    <i class="bi bi-graph-up text-info fs-4"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">{{ __('messages.Pending refund requests') }}</div>
                        <div class="fs-4 fw-semibold">{{ $report['kpis']['pending_refund_requests'] ?? 0 }}</div>
                    </div>
                    <i class="bi bi-inbox text-primary fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="card-title mb-0">{{ __('messages.Daily paid revenue') }}</h5>
        </div>
        <div class="card-body p-0 table-scroll-x">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('messages.Date') }}</th>
                        <th class="text-end">{{ __('messages.Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['daily_sales'] as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td class="text-end">{{ number_format($row['total'], 2) }} {{ $currency }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted py-4">{{ __('messages.No data.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">{{ __('messages.Filter earnings') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <form method="GET" action="{{ route('v1.admin.reports.earnings') }}">
                @include('v1.admin.reports.partials.date-fields', ['filters' => $filters])
                <div class="d-flex gap-2">
                    <a href="{{ route('v1.admin.reports.earnings') }}" class="btn btn-outline-secondary flex-fill">{{ __('messages.Reset') }}</a>
                    <button type="submit" class="btn btn-primary flex-fill">{{ __('messages.Apply') }}</button>
                </div>
            </form>
        </div>
    </div>
@endpush



