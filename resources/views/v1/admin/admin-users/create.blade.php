@extends('layouts.app')
@section('title', __('messages.New admin'))
@section('heading', __('messages.New admin'))
@section('content')
    <form action="{{ route('v1.admin.admin-users.store') }}" method="POST" class="max-w-xl">
        @include('v1.admin.admin-users._form')
    </form>
@endsection
