@extends('layouts.app')

@php
    $page = 'goals';
@endphp

@section('title', __('messages.Goals'))
@section('subtitle', __('messages.Manage goals'))
@section('page-actions')
    <a href="{{ route('v1.admin.goals.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>{{ __('messages.New goal') }}
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body p-0">
            @include('v1.admin.goals.partials.table', ['goals' => $goals])
        </div>
    </div>
@endsection
