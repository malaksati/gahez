@extends('layouts.app')
@section('title', __('messages.Edit offer'))
@section('heading', __('messages.Edit offer'))
@section('content')
    <form action="{{ route('v1.admin.offers.update', $offer) }}" method="POST" class="card card-body col-lg-8">@method('PUT')@include('v1.admin.offers._form')</form>
@endsection