@extends('layouts.app')

@section('title', __('messages.Branches'))
@section('subtitle', __('messages.Manage branches'))
@section('page-actions')
    <a href="{{ route('v1.admin.branches.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>{{ __('messages.New branch') }}
    </a>
@endsection

@section('content')
    @include('v1.admin.branches.partials.filters')
    <div data-admin-list-results>
        @include('v1.admin.branches.partials.results', ['branches' => $branches])
    </div>
@endsection
