@extends('layouts.app')

@section('title', __('messages.New customer'))
@section('subtitle', __('messages.Create a new customer account'))

@section('content')
    <form action="{{ route('v1.admin.customers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('v1.admin.customers._form')
        
        <div class="mt-4 pb-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2 me-1"></i>{{ __('messages.Create customer') }}
            </button>
            <a href="{{ route('v1.admin.customers.index') }}" class="btn btn-outline-secondary">
                {{ __('messages.Cancel') }}
            </a>
        </div>
    </form>
@endsection
