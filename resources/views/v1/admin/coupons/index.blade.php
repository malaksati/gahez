@extends('layouts.app')

@section('title', __('messages.Coupons'))
@section('subtitle', __('messages.Manage coupons'))
@section('page-actions')
    <a href="{{ route('v1.admin.coupons.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>{{ __('messages.New coupon') }}
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body p-0">
            @include('v1.admin.coupons.partials.table', ['coupons' => $coupons])
        </div>
    </div>
@endsection
