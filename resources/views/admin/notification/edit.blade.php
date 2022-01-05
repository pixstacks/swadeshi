@extends('admin.layout.app')

@section('title')
    Admin - {{ __('crud.admin.notifications.update') }}
@endsection

@section('heading')
    {{ __('crud.admin.notifications.update') }}
@endsection

@section('content')
    <div class="w-full mb-5 bg-white rounded-lg shadow-xs dark:text-gray-400 dark:bg-gray-800">
        <div class="w-full px-5 py-5">
            <x-form action="{{ route('admin.notification.update', $notification) }}" method="put" has-file>
                @include('admin.notification.form-inputs')
                
                <div class="flex justify-end">
                    <button type="submit" class="right-0 inline-block px-4 py-1 text-sm font-semibold leading-loose text-white transition duration-200 bg-green-500 rounded-lg hover:bg-green-600" type="submit">{{ __('crud.general.update') }} {{ __('crud.admin.notifications.name') }}</button>
                </div>
            </x-form>
        </div>
    </div>
@endsection