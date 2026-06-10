@extends('layouts.app')
@section('title', __('messages.New variant option'))
@section('heading', __('messages.New variant option'))
@section('content')
    <form action="{{ route('v1.admin.variant-options.store') }}" method="POST" class="max-w-xl rounded-lg border bg-white p-6">@include('v1.admin.variant-options._form')</form>
@endsection