@extends('layouts.app')

@section('title', __('messages.Edit goal'))
@section('heading', __('messages.Edit goal'))
@section('content')
    <form action="{{ route('v1.admin.goals.update', $goal) }}" method="POST" class="card card-body col-lg-8">
        @method('PUT')
        @include('v1.admin.goals._form', ['goal' => $goal])
    </form>
@endsection
