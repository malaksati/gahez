@extends('layouts.app')

@section('title', __('messages.Variants'))
@section('subtitle', __('messages.Manage variants'))

@section('page-actions')
    <a href="{{ route('v1.admin.variants.export', request()->query()) }}" class="btn btn-outline-primary">
        <i class="bi bi-download me-1"></i>{{ __('messages.Export') }}
    </a>
    <a href="{{ route('v1.admin.variants.import') }}" class="btn btn-outline-success">
        <i class="bi bi-upload me-1"></i>{{ __('messages.Import') }}
    </a>
    <a href="{{ route('v1.admin.variants.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>{{ __('messages.New variant') }}
    </a>
@endsection

@section('content')
    <x-import-export-layout>
        @include('v1.admin.variants.partials.filters')
        <div data-admin-list-results>
            @include('v1.admin.variants.partials.results', ['variants' => $variants])
        </div>
    </x-import-export-layout>
@endsection
