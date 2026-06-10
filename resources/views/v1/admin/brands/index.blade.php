@extends('layouts.app')

@section('title', __('messages.Brands'))
@section('subtitle', __('messages.Manage brands'))
@section('page-actions')
    <a href="{{ route('v1.admin.brands.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>{{ __('messages.New brand') }}
    </a>
@endsection

@section('content')
    @include('v1.admin.brands.partials.filters')
    <div data-admin-list-results>
        @include('v1.admin.brands.partials.results', ['brands' => $brands])
    </div>
@endsection
