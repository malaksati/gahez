@extends('layouts.app')

@php
    $page = 'reports';
    $currency = display_currency();
    $k = $report['kpis'];
@endphp

@section('title', __('messages.Product performance'))
@section('subtitle', __('messages.Order revenue and volume for the selected period'))

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
                <div class="card-body">
                    <div class="text-muted small">{{ __('messages.Revenue (filtered)') }}</div>
                    <div class="fs-4 fw-semibold">{{ format_local_number($k['revenue'] ?? 0, 2) }} {{ $currency }}</div>
                    <small class="text-muted">{{ __('messages.Orders') }}: @num($k['orders_count'] ?? 0)</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">{{ __('messages.Orders count') }}</div>
                    <div class="fs-4 fw-semibold">@num($k['quantity'] ?? 0)</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">{{ __('messages.Net (after commission)') }}</div>
                    <div class="fs-4 fw-semibold">{{ format_local_number($k['net'] ?? 0, 2) }} {{ $currency }}</div>
                    <small class="text-muted">{{ __('messages.Commission') }}: {{ format_local_number($k['commission'] ?? 0, 2) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">{{ __('messages.Refunded') }}</div>
                    <div class="fs-4 fw-semibold">{{ format_local_number($k['refunded_amount'] ?? 0, 2) }} {{ $currency }}</div>
                    <small class="text-muted">{{ __('messages.Refunded orders') }}: @num($k['refunded_orders'] ?? 0)</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="card-title mb-0">{{ __('messages.Daily paid sales') }}</h5>
        </div>
        <div class="card-body p-0 table-scroll-x">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('messages.Date') }}</th>
                        <th class="text-end">{{ __('messages.Revenue') }}</th>
                        <th class="text-end">{{ __('messages.Qty') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['daily_sales'] as $row)
                        <tr>
                            <td>@digits($row['date'])</td>
                            <td class="text-end">{{ format_local_number($row['total'], 2) }} {{ $currency }}</td>
                            <td class="text-end">@num($row['qty'])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">{{ __('messages.No data.') }}</td>
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
            <h5 class="offcanvas-title">{{ __('messages.Filter product performance') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <form method="GET" action="{{ route('v1.admin.reports.product-performance') }}">
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.Product') }}</label>
                    <select name="product_id" class="form-select">
                        <option value="">{{ __('messages.All products') }}</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((string) ($filters['product_id'] ?? '') === (string) $product->id)>
                                #{{ $product->id }} — {{ $product->getTranslation('name', app()->getLocale()) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.Category') }}</label>
                    <select name="category_id" class="form-select">
                        <option value="">{{ __('messages.All categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>
                                {{ $category->getTranslation('name', app()->getLocale()) }}
                            </option>
                            @foreach ($category->children as $child)
                                <option value="{{ $child->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $child->id)>
                                    — {{ $child->getTranslation('name', app()->getLocale()) }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.Payment') }}</label>
                    <select name="payment_status" class="form-select">
                        <option value="">{{ __('messages.Paid (default)') }}</option>
                        <option value="pending" @selected(($filters['payment_status'] ?? '') === 'pending')>{{ __('messages.pending') }}</option>
                        <option value="paid" @selected(($filters['payment_status'] ?? '') === 'paid')>{{ __('messages.paid') }}</option>
                        <option value="failed" @selected(($filters['payment_status'] ?? '') === 'failed')>{{ __('messages.failed') }}</option>
                        <option value="refunded" @selected(($filters['payment_status'] ?? '') === 'refunded')>{{ __('messages.refunded') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.Status') }}</label>
                    <select name="order_status" class="form-select">
                        <option value="">{{ __('messages.All') }}</option>
                        @foreach (['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'] as $status)
                            <option value="{{ $status }}" @selected(($filters['order_status'] ?? '') === $status)>{{ __('messages.'.$status) }}</option>
                        @endforeach
                    </select>
                </div>
                @include('v1.admin.reports.partials.date-fields', ['filters' => $filters])
                <div class="d-flex gap-2">
                    <a href="{{ route('v1.admin.reports.product-performance') }}" class="btn btn-outline-secondary flex-fill">{{ __('messages.Reset') }}</a>
                    <button type="submit" class="btn btn-primary flex-fill">{{ __('messages.Apply') }}</button>
                </div>
            </form>
        </div>
    </div>
@endpush

