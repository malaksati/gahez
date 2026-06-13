@extends('layouts.app')

@section('title', __('messages.Order refund requests'))
@section('subtitle', __('messages.Manage order refund requests'))

@section('content')
    @include('v1.admin.order-refund-requests.partials.filters')
    <div data-admin-list-results>
        @include('v1.admin.order-refund-requests.partials.results', ['refundRequests' => $refundRequests])
    </div>
@endsection
