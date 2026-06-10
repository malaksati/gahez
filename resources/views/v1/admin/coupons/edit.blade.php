@extends('layouts.app')

@section('title', __('messages.Edit').' — '.$coupon->code)
@section('subtitle', __('messages.Coupons'))

@section('content')
    <form action="{{ route('v1.admin.coupons.update', $coupon) }}" method="POST" class="card card-body col-lg-8">
        @method('PUT')
        @include('v1.admin.coupons._form')
    </form>
@endsection
