@extends('layouts.app')
@section('title', __('messages.New offer'))
@section('heading', __('messages.New offer'))
@section('content')
    <form action="{{ route('v1.admin.offers.store') }}" method="POST" class="card card-body col-lg-8">@include('v1.admin.offers._form')</form>
@endsection