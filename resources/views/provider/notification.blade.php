@extends('provider.layout.app')

@section('title')
    Notifications
@endsection

@section('content')
    @livewire('notification-page', [
        'notificationId' => request()->has('notificationId') ? request()->notificationId : NULL,
        'userType' => 'provider'
    ])
@endsection