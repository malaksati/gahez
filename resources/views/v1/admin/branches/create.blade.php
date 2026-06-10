@extends('layouts.app')
@section('title', __('messages.New branch'))
@section('heading', __('messages.New branch'))
@section('content')
    <form action="{{ route('v1.admin.branches.store') }}" method="POST" class="max-w-xl rounded-lg border bg-white p-6">@include('v1.admin.branches._form')</form>
@endsection