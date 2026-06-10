@extends('layouts.app')

@section('title', __('messages.Customer Users'))
@section('subtitle', __('messages.Manage customer users'))
@section('page-actions')
    <a href="{{ route('v1.admin.customers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>{{ __('messages.New customer') }}
    </a>
@endsection

@section('content')
    @include('v1.admin.customers.partials.filters')
    <div data-admin-list-results>
        @include('v1.admin.customers.partials.results', ['customers' => $customers])
    </div>
@endsection
