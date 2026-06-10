@extends('layouts.app')

@section('title', __('messages.New goal'))
@section('heading', __('messages.New goal'))
@section('content')
    <form action="{{ route('v1.admin.goals.store') }}" method="POST" class="card card-body col-lg-8">
        @include('v1.admin.goals._form')
    </form>
@endsection
