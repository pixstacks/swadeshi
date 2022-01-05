@extends('user.layout.app')

@section('content')
    <section class="relative py-20">
        <img class="hidden lg:block absolute top-0 left-0 mt-16 z-10" src="{{ asset('img/assets/icons/dots/blue-dot-left-bars.svg') }}" alt="">
        <div class="absolute top-0 left-0 lg:bottom-0 h-128 lg:h-auto w-full lg:w-8/12 bg-cover bg-no-repeat bg-gray-800 opacity-50" @if(array_key_exists('login_provider_bg_img', $settings)) style="background-image: url('{{ asset('storage/'.$settings['login_provider_bg_img']) }}');" @endif></div>
        <div class="relative container px-4 mx-auto">
            <div class="flex flex-wrap items-center -mx-4">
                <div class="w-full lg:w-1/2 px-4 mb-12 lg:mb-0">
                    <div class="max-w-lg">
                        <h2 class="mb-1 dark:text-gray-100 text-4xl font-semibold font-heading">Reset Password</h2>
                        <p class="text-xl dark:text-gray-200 text-gray-800">Reset Your Password Here & Continue Experiencing Amazing Rides With Us.</p>
                    </div>
                </div>
                <div class="w-full lg:w-1/2 px-4">
                    <div class="lg:max-w-md p-6 lg:px-10 lg:py-12 bg-white dark:bg-gray-900 text-center border dark:border-gray-800 rounded-xl" x-show.transition.in="!forgotPassword">
                        <form action="{{ route('provider.password.update') }}" method="post">
                            <h3 class="mb-8 text-3xl font-semibold font-heading dark:text-blue-100">Reset Password</h3>
                            
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="relative flex flex-wrap mb-6">
                                <input class="relative bg-gray-50 mb-2 md:mb-0 w-full dark:bg-gray-700 dark:text-gray-200 outline-none py-4 pl-4 text-sm rounded" type="email" value="{{ $email ?? old('email') }}" placeholder="e.g hello@example.dev" name="email" required autofocus>
                                <span class="absolute top-0 left-0 ml-4 -mt-2 px-1 inline-block bg-transparent text-gray-500 dark:text-gray-300 text-sm">{{ __('crud.inputs.email') }}</span>
                            </div>

                            <div class="relative flex flex-wrap mb-6">
                                <input class="relative bg-gray-50 mb-2 md:mb-0 w-full dark:bg-gray-700 dark:text-gray-200 py-4 pl-4 text-sm rounded outline-none" type="password" placeholder="******" name="password" required>
                                <span class="absolute top-0 left-0 ml-4 -mt-2 px-1 inline-block bg-transparent text-gray-500 dark:text-gray-300 text-sm">{{ __('crud.inputs.password') }}</span>
                            </div>

                            <div class="relative flex flex-wrap mb-6">
                                <input class="relative bg-gray-50 mb-2 md:mb-0 w-full dark:bg-gray-700 dark:text-gray-200 py-4 pl-4 text-sm rounded outline-none" type="password" placeholder="******" name="password_confirmation" required>
                                <span class="absolute top-0 left-0 ml-4 -mt-2 px-1 inline-block bg-transparent text-gray-500 dark:text-gray-300 text-sm">{{ __('crud.inputs.confirm_password') }}</span>
                            </div>
                            @csrf

                            <button class="w-full inline-block py-4 text-sm text-white font-medium leading-normal bg-red-400 hover:bg-red-300 rounded transition duration-200">{{ __('crud.general.change_password') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
