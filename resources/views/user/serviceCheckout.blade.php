@extends('user.layout.app')

@section('title')
    {{ __('crud.general.checkout') }}
@endsection

@push('startScripts')
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('constants.map_key') }}&libraries=places,geocoding"></script>

    {{-- Ratings --}}
    <link rel="stylesheet" href="{{ asset('css/starability-all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/starability-slot.min.css') }}">
    <style>
        #map {
            height: 300px;
        }
        @media only screen and (min-width: 600px) {
            #map{
                height: 500px;
            }
        }
    </style>
@endpush

@section('content')
    <section class="h-full">
        <div class="grid grid-cols-5 gap-4 h-4/5">
            <div class="md:col-span-3 col-span-5">
                <div id="map" style="" wire:ignore></div>
            </div>
            @livewire('user.user-checkout')
        </div>
    </section>
@endsection

@push('endScripts')
    <script src="{{ asset('js/user/requestHandler.js') }}"></script>
@endpush