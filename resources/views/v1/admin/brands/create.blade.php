@extends('layouts.app')
@section('title', __('messages.New brand'))
@section('heading', __('messages.New brand'))
@section('content')
    <form action="{{ route('v1.admin.brands.store') }}" method="POST" class="max-w-xl rounded-lg border bg-white p-6">@include('v1.admin.brands._form')</form>
@endsection