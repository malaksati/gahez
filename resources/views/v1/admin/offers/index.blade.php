@extends('layouts.app')

@section('title', __('messages.Offers'))
@section('subtitle', __('messages.Manage offers'))
@section('page-actions')
    <a href="{{ route('v1.admin.offers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>{{ __('messages.New offer') }}
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body p-0">
            @include('v1.admin.offers.partials.table', ['offers' => $offers])
        </div>
    </div>
@endsection
