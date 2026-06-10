@extends('layouts.app')
@section('title', __('messages.Edit variant option'))
@section('heading', __('messages.Edit variant option'))
@section('content')
    <form action="{{ route('v1.admin.variant-options.update', $variantOption) }}" method="POST" class="max-w-xl rounded-lg border bg-white p-6">@method('PUT')@include('v1.admin.variant-options._form')</form>
@endsection