@extends('layouts.app')

@section('title', __('messages.New coupon'))
@section('subtitle', __('messages.Coupons'))

@section('content')
    <form action="{{ route('v1.admin.coupons.store') }}" method="POST" class="card card-body col-lg-8">
        @include('v1.admin.coupons._form')
    </form>
@endsection
