@extends('layouts.app')

@section('title', __('messages.Tickets'))
@section('subtitle', __('messages.Manage tickets'))

@section('content')
    @include('v1.admin.tickets.partials.filters')
    <div data-admin-list-results>
        @include('v1.admin.tickets.partials.results', ['tickets' => $tickets])
    </div>
@endsection
