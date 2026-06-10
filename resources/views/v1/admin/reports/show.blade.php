@extends('layouts.app')

@php
    $page = 'reports';
    $currency = app_currency();
    $needsDateRange = in_array($type, [
        'sales-period',
        'sales-payment-methods',
        'top-products-categories',
        'deliveries',
        'zones-demand',
        'zones-activity',
    ], true);
    $isManualPeriod = ($filters['period_type'] ?? 'monthly') === 'manual';
@endphp

@section('title', $report['title'])
@section('subtitle', __('messages.Report details'))

@section('page-actions')
    <a href="{{ route('v1.admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>{{ __('messages.Back to reports') }}
    </a>
    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
        <i class="bi bi-sliders me-1"></i>{{ __('messages.Filters') }}
    </button>
    <a
        href="{{ route('v1.admin.reports.export', array_merge(['type' => $type], request()->query())) }}"
        class="btn btn-success btn-sm"
    >
        <i class="bi bi-file-earmark-excel me-1"></i>{{ __('messages.Export Excel') }}
    </a>
    <a
        href="{{ route('v1.admin.reports.export-pdf', array_merge(['type' => $type], request()->query())) }}"
        class="btn btn-danger btn-sm"
        target="_blank"
    >
        <i class="bi bi-file-earmark-pdf me-1"></i>{{ __('messages.Export PDF') }}
    </a>
@endsection

@section('content')
    @if ($needsDateRange)
        @include('v1.admin.reports.partials.period-buttons', ['type' => $type, 'filters' => $filters])
        @if ($isManualPeriod)
            <div class="alert alert-light border small mb-3">
                <i class="bi bi-calendar-range me-1"></i>{{ __('messages.Manual range hint') }}
            </div>
        @endif
    @endif

    @if (! empty($report['summary']))
        <div class="row g-3 mb-4">
            @if ($type === 'customers')
                @foreach ([
                    ['label' => __('messages.Total customers'), 'value' => $report['summary']['total'] ?? 0],
                    ['label' => __('messages.Active customers'), 'value' => $report['summary']['active'] ?? 0],
                    ['label' => __('messages.Inactive customers'), 'value' => $report['summary']['inactive'] ?? 0],
                ] as $kpi)
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-muted small">{{ $kpi['label'] }}</div>
                                <div class="fs-4 fw-semibold">{{ $kpi['value'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @elseif ($type === 'sales-period')
                @foreach ([
                    ['label' => __('messages.Orders'), 'value' => $report['summary']['orders_count'] ?? 0],
                    ['label' => __('messages.Revenue'), 'value' => number_format($report['summary']['revenue'] ?? 0, 2).' '.$currency],
                    ['label' => __('messages.Average demand'), 'value' => number_format($report['summary']['avg_order'] ?? 0, 2).' '.$currency],
                    ['label' => __('messages.Shipments'), 'value' => $report['summary']['shipments'] ?? 0],
                    ['label' => __('messages.Shipping revenue'), 'value' => number_format($report['summary']['shipping_revenue'] ?? 0, 2).' '.$currency],
                ] as $kpi)
                    <div class="col-md-6 col-xl-4 col-xxl">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-muted small">{{ $kpi['label'] }}</div>
                                <div class="fs-5 fw-semibold">{{ $kpi['value'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @elseif ($type === 'sales-payment-methods')
                @foreach ([
                    ['label' => __('messages.Total orders'), 'value' => $report['summary']['total_orders'] ?? 0],
                    ['label' => __('messages.Total revenue'), 'value' => number_format($report['summary']['total_revenue'] ?? 0, 2).' '.$currency],
                ] as $kpi)
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-muted small">{{ $kpi['label'] }}</div>
                                <div class="fs-4 fw-semibold">{{ $kpi['value'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">{{ $report['title'] }}</h5>
                @if (! empty($filters['resolved_from']) && ! empty($filters['resolved_to']))
                    <small class="text-muted">
                        {{ \Carbon\Carbon::parse($filters['resolved_from'])->format('d-m-Y') }}
                        —
                        {{ \Carbon\Carbon::parse($filters['resolved_to'])->format('d-m-Y') }}
                    </small>
                @endif
            </div>
            <span class="badge text-bg-light">{{ count($report['rows']) }} {{ __('messages.Rows') }}</span>
        </div>
        <div class="card-body p-0 table-scroll-x">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        @foreach ($report['headings'] as $heading)
                            <th>{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['rows'] as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($report['headings']) }}" class="text-center text-muted py-4">
                                {{ __('messages.No data.') }}
                            </td>
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
            <h5 class="offcanvas-title">{{ __('messages.Filter reports') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <form method="GET" action="{{ route('v1.admin.reports.show', $type) }}">
                @if ($needsDateRange)
                    <input type="hidden" name="period_type" value="manual">
                    @include('v1.admin.reports.partials.date-fields', [
                        'filters' => [
                            'from_date' => $filters['from_date'] ?? '',
                            'to_date' => $filters['to_date'] ?? '',
                        ],
                    ])
                @endif

                <div class="d-flex gap-2">
                    <a href="{{ route('v1.admin.reports.show', $type) }}" class="btn btn-outline-secondary flex-fill">{{ __('messages.Reset') }}</a>
                    <button type="submit" class="btn btn-primary flex-fill">{{ __('messages.Apply') }}</button>
                </div>
            </form>
        </div>
    </div>
@endpush
