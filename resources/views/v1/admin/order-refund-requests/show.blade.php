@extends('layouts.app')

@php
    $page = 'order-refund-requests';
    $statusBadge = match ($refundRequest->status) {
        'approved' => 'success',
        'rejected' => 'danger',
        'pending' => 'warning',
        default => 'secondary',
    };
    $badges = '<span class="badge bg-'.$statusBadge.' text-capitalize">'.__('messages.'.$refundRequest->status).'</span>';
@endphp

@section('title', __('messages.Refund request').' #'.$refundRequest->id)
@section('heading', __('messages.Refund request details'))

@section('content')
    @include('v1.admin.partials.show-header', [
        'indexRoute' => 'v1.admin.order-refund-requests.index',
        'indexLabel' => __('messages.Refund requests'),
        'title' => __('messages.Refund request').' #'.$refundRequest->id,
        'badges' => $badges,
        'editRoute' => route('v1.admin.order-refund-requests.edit', $refundRequest),
        'editLabel' => __('messages.Edit refund request'),
    ])

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('messages.Details') }}</h5>
                    <div class="row g-3">
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Order'),
                            'value' => '<a href="'.route('v1.admin.orders.show', $refundRequest->order_id).'">#'.e((string) $refundRequest->order_id).'</a>',
                        ])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Customer'),
                            'value' => e($refundRequest->user?->name ?? '—'),
                        ])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Reason'),
                            'value' => e($refundRequest->reason ?: '—'),
                            'col' => 'col-12',
                        ])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Details'),
                            'value' => e($refundRequest->details ?: '—'),
                            'col' => 'col-12',
                        ])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Processed by'),
                            'value' => e($refundRequest->processor?->name ?? '—'),
                        ])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Processed at'),
                            'value' => $refundRequest->processed_at?->format('M d, Y H:i') ?? '—',
                        ])
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            @include('v1.admin.partials.show-timestamps', ['model' => $refundRequest])
        </div>
    </div>
@endsection
