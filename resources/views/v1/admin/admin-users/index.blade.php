@extends('layouts.app')

@section('title', __('messages.Admin Users'))
@section('subtitle', __('messages.Manage admin users'))
@section('page-actions')
    <a href="{{ route('v1.admin.admin-users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>{{ __('messages.New admin') }}
    </a>
@endsection

@section('content')
    @include('v1.admin.admin-users.partials.filters')
    <div data-admin-list-results>
        @include('v1.admin.admin-users.partials.results', ['admins' => $admins])
    </div>
@endsection
