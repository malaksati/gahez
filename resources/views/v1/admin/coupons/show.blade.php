@extends('layouts.app')

@php
    $page = 'coupons';
    $currency = display_currency();
    $badges = view('v1.admin.partials.active-badge', ['active' => $coupon->is_active])->render();
@endphp

@section('title', $coupon->code)
@section('heading', __('messages.Coupon details'))

@section('content')
    @include('v1.admin.partials.show-header', [
        'indexRoute' => 'v1.admin.coupons.index',
        'indexLabel' => __('messages.Coupons'),
        'title' => $coupon->code,
        'badges' => $badges,
        'editRoute' => route('v1.admin.coupons.edit', $coupon),
        'editLabel' => __('messages.Edit coupon'),
        'destroyRoute' => route('v1.admin.coupons.destroy', $coupon),
    ])

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('messages.Details') }}</h5>
                    <div class="row g-3">
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Code'), 'value' => '<code>'.e($coupon->code).'</code>'])
                        <div class="col-md-6">
                            <small class="text-muted d-block">{{ __('messages.Discount type') }}</small>
                            <p class="mb-0">@include('v1.admin.partials.discount-type-badge', ['type' => $coupon->type])</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">{{ __('messages.Discount value') }}</small>
                            <p class="mb-0">@include('v1.admin.partials.discount-value', ['type' => $coupon->type, 'amount' => $coupon->discount_value])</p>
                        </div>
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Min cart amount'),
                            'value' => $coupon->min_cart_amount > 0
                                ? format_local_number((float) $coupon->min_cart_amount, 2).($currency ? ' '.$currency : '')
                                : '—',
                        ])
                        @php
                            $ordersUsed = (int) ($coupon->orders_count ?? $coupon->totalOrdersUsed());
                            $totalLimitLabel = $coupon->usage_limit
                                ? $ordersUsed.' / '.$coupon->usage_limit.' '.strtolower(__('messages.Orders'))
                                : __('messages.Unlimited').($ordersUsed > 0 ? ' ('.$ordersUsed.' '.__('messages.used').')' : '');
                            $perUserLimitLabel = $coupon->usage_limit_per_user
                                ? $coupon->usage_limit_per_user.' '.__('messages.per user')
                                : __('messages.Unlimited');
                        @endphp
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Total order limit'),
                            'value' => $totalLimitLabel,
                        ])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Usage limit per user'),
                            'value' => $perUserLimitLabel,
                        ])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.First order only'),
                            'value' => $coupon->first_order_only ? __('messages.Yes') : __('messages.No'),
                        ])
                        <div class="col-md-6">
                            <small class="text-muted d-block">{{ __('messages.Validity') }}</small>
                            <p class="mb-0">@include('v1.admin.partials.validity-period', ['model' => $coupon])</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">{{ __('messages.Status') }}</small>
                            <p class="mb-0">@include('v1.admin.partials.active-badge', ['active' => $coupon->is_active])</p>
                        </div>
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Orders'), 'value' => (string) ($coupon->orders_count ?? 0)])
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            @include('v1.admin.partials.show-timestamps', ['model' => $coupon])
        </div>
    </div>
@endsection
