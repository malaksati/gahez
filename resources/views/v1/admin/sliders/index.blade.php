@extends('layouts.app')

@section('title', __('messages.Sliders'))
@section('subtitle', __('messages.Manage sliders'))
@section('page-actions')
    <a href="{{ route('v1.admin.sliders.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>{{ __('messages.New slider') }}
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body p-0">
            @include('v1.admin.sliders.partials.table', ['sliders' => $sliders])
        </div>
    </div>
@endsection
