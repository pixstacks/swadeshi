@extends('provider.layout.app')

@section('title')
    Provider {{ __('crud.navlinks.dashboard') }}
@endsection

@section('heading')
    {{ __('crud.navlinks.dashboard') }}
@endsection

@section('content')
    <!-- Cards -->
    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4 px-6">
        <!-- Card -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <div class="p-3 mr-4 flex justify-center items-center text-blue-100 bg-blue-500 rounded-full dark:text-orange-100 dark:bg-blue-500 w-12 h-12">
                <i class="fa fa-dollar text-2xl text-white" style="font-size: 1.2rem;"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                    Revenue
                </p>
                <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                    {{ currency($total_revenue) }}
                </p>
            </div>
        </div>
        <!-- Card -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <div class="p-3 mr-4 flex justify-center items-center text-yellow-100 bg-yellow-500 rounded-full dark:text-yellow-100 dark:bg-yellow-500 w-12 h-12">
                <i class="fa fa-history"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                    Revenue of the Month
                </p>
                <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                    {{ currency($month_revenue) }}
                </p>
            </div>
        </div>
        <!-- Card -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <div class="p-3 mr-4 flex justify-center items-center text-purple-100 bg-purple-500 rounded-full dark:text-purple-100 dark:bg-purple-500 w-12 h-12">
                <i class="fa fa-rocket"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                    Total No. Of Rides
                </p>
                <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                    {{ $cancelled_rides + $completed_rides }}
                </p>
            </div>
        </div>
        <!-- Card -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <div class="p-3 mr-4 flex justify-center items-center text-green-100 bg-green-500 rounded-full dark:text-green-100 dark:bg-green-500 w-12 h-12">
                <i class="fa fa-check"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                    Completed Travels
                </p>
                <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                    {{ $completed_rides }}
                </p>
            </div>
        </div>
        <!-- Card -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <div class="p-3 mr-4 flex justify-center items-center text-red-100 bg-red-500 rounded-full dark:text-red-100 dark:bg-red-500 w-12 h-12">
                <i class="fa fa-times"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                    Cancelled Rides
                </p>
                <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                    {{ $cancelled_rides }}
                </p>
            </div>
        </div>
    </div>
    
    <div>
        @livewire('provider.go-offline')
    </div>
@endsection