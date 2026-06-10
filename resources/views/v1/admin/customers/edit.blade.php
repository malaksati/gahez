@extends('layouts.app')

@section('title', __('messages.Edit customer'))
@section('subtitle', $customer->name)

@section('content')
    <form action="{{ route('v1.admin.customers.update', $customer) }}" method="POST">
        @csrf
        @method('PUT')
        @include('v1.admin.customers._form', ['customer' => $customer])
        
        <div class="mt-4 pb-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2 me-1"></i>{{ __('messages.Save changes') }}
            </button>
            <a href="{{ route('v1.admin.customers.index') }}" class="btn btn-outline-secondary">
                {{ __('messages.Cancel') }}
            </a>
        </div>
    </form>
@endsection
