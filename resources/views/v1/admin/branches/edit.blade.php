@extends('layouts.app')
@section('title', __('messages.Edit branch'))
@section('heading', __('messages.Edit branch'))
@section('content')
    <form action="{{ route('v1.admin.branches.update', $branch) }}" method="POST" class="max-w-xl rounded-lg border bg-white p-6">@method('PUT')@include('v1.admin.branches._form')</form>
@endsection