@extends('layouts.app')
@section('title', __('messages.Edit admin'))
@section('heading', __('messages.Edit admin'))
@section('content')
    <form action="{{ route('v1.admin.admin-users.update', $admin) }}" method="POST" class="max-w-xl">
        @method('PUT')
        @include('v1.admin.admin-users._form')
    </form>
@endsection
