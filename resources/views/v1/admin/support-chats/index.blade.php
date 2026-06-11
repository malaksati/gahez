@extends('layouts.app')

@section('title', __('messages.Support chats'))
@section('subtitle', __('messages.Manage support chats'))

@section('content')
    @include('v1.admin.support-chats.partials.filters')
    <div data-admin-list-results>
        @include('v1.admin.support-chats.partials.results', ['supports' => $supports])
    </div>
@endsection
