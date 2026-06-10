@extends('layouts.app')
@section('title', __('messages.Edit brand'))
@section('heading', __('messages.Edit brand'))
@section('content')
    <form action="{{ route('v1.admin.brands.update', $brand) }}" method="POST" class="max-w-xl rounded-lg border bg-white p-6">@method('PUT')@include('v1.admin.brands._form')</form>
@endsection