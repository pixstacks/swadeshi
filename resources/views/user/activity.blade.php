@extends('user.layout.app')

@section('title')
    {{ __('crud.general.blog') }}
@endsection

@push('startScripts')
    <link rel="stylesheet" href="{{ asset('css/starability-all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/starability-slot.min.css') }}">
@endpush

@section('content')
    {{-- Todo: Make Scheduled Rides Section Working in This Livewire Component. --}}
    @livewire('user.activity', [
        'user' => auth()->user(),
        'show' => request()->get('show'),
    ])
@endsection