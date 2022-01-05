@extends('user.layout.app')

@section('title')
    {{ __('crud.general.provider') }} {{ __('crud.general.login') }}
@endsection

@section('content')
    <section class="relative py-20" x-data="alpineWalletVars()" x-cloak>
        <img class="hidden lg:block absolute top-0 left-0 mt-16 z-10" src="{{ asset('img/assets/icons/dots/blue-dot-left-bars.svg') }}" alt="">
        <div class="absolute top-0 left-0 lg:bottom-0 h-128 lg:h-auto w-full lg:w-8/12 bg-cover bg-no-repeat bg-gray-800 opacity-50" @if(array_key_exists('login_provider_bg_img', $settings)) style="background-image: url('{{ asset('storage/'.$settings['login_provider_bg_img']) }}');" @endif></div>
        <div class="relative container px-4 mx-auto">
            <div class="flex flex-wrap items-center -mx-4">
                <div class="w-full lg:w-1/2 px-4 mb-12 lg:mb-0">
                    <div class="max-w-lg">
                        <h2 class="mb-1 dark:text-gray-100 text-4xl font-semibold font-heading">{{ array_key_exists('login_provider_heading', $settings) ? $settings['login_provider_heading'] : 'Dummy Heading Here' }}</h2>
                        <p class="text-xl dark:text-gray-200 text-gray-800">{{ array_key_exists('login_provider_desc', $settings) ? $settings['login_provider_desc'] : '' }}</p>
                    </div>
                </div>
                <div class="w-full lg:w-1/2 px-4">
                    <div class="lg:max-w-md p-6 lg:px-10 lg:py-12 bg-white dark:bg-gray-900 text-center border dark:border-gray-800 rounded-xl" x-show.transition.in="!forgotPassword">
                        <form action="{{ route('provider.login') }}" method="post">
                            <span class="inline-block mb-4 text-xs text-blue-400 dark:text-gray-400 font-semibold">Sign In</span>
                            <h3 class="mb-12 text-3xl font-semibold font-heading dark:text-blue-100">Join our community</h3>
                            
                            <div class="relative flex flex-wrap mb-6">
                                <input class="relative bg-gray-50 mb-2 md:mb-0 w-full dark:bg-gray-700 dark:text-gray-200 outline-none py-4 pl-4 text-sm rounded"type="email" value="{{ old('email', 'partner@dragon.com') }}" placeholder="e.g hello@example.dev" name="email" required autofocus>
                                <span class="absolute top-0 left-0 ml-4 -mt-2 px-1 inline-block bg-transparent text-gray-500 dark:text-gray-300 text-sm">{{ __('crud.inputs.email') }}</span>
                            </div>
                            
                            <div class="relative flex flex-wrap mb-1">
                                <input class="relative bg-gray-50 mb-2 md:mb-0 w-full dark:bg-gray-700 dark:text-gray-200 py-4 pl-4 text-sm rounded outline-none" type="password" placeholder="******" name="password" required value="password">
                                <span class="absolute top-0 left-0 ml-4 -mt-2 px-1 inline-block bg-transparent text-gray-500 dark:text-gray-300 text-xs">{{ __('crud.inputs.password') }}</span>
                            </div>
                            
                            <a @click="toggleForgotPassword" class="block pb-6 text-sm float-right font-semibold hover:underline dark:text-gray-300" href="javascript:void(0)">Forgot password?</a>

                            @csrf
                            <button class="w-full inline-block py-4 mb-4 text-sm text-white font-medium leading-normal bg-red-400 hover:bg-red-300 rounded transition duration-200">{{ __('crud.general.login') }}</button>

                            <div class="dark:text-gray-200 my-4">
                                OR
                            </div>

                            {{-- Google Registration --}}
                            <a class="flex items-center justify-center mb-4 py-4 bg-yellow-800 hover:bg-yellow-900 rounded" href="{{ route('user.google.login') }}">
                                <span class="inline-block mr-2">
                                    <i class="text-white text-sm fa fa-google"></i>
                                </span>
                                <span class="text-sm text-white">Sign in with Google</span>
                            </a>

                            {{-- Registration --}}
                            <a class="flex items-center justify-center mb-4 py-4 bg-indigo-800 hover:bg-indigo-900 rounded" href="{{ route('provider.register') }}">
                                <span class="text-sm text-white">New Here? Register</span>
                            </a>
                        </form>
                    </div>
                    <div class="lg:max-w-md p-6 lg:px-10 lg:py-12 bg-white dark:bg-gray-900 text-center border dark:border-gray-800 rounded-xl" x-show.transition.in="forgotPassword">
                        <form action="{{ route('provider.password.email') }}" method="post">
                            <span class="inline-block mb-1 text-xs text-blue-400 dark:text-gray-400 font-semibold">Forgot Password?</span>
                            <h3 class="mb-8 text-3xl font-semibold font-heading dark:text-blue-100">Change Password</h3>
                            
                            <div class="relative flex flex-wrap mb-6">
                                <input class="relative bg-gray-50 mb-2 md:mb-0 w-full dark:bg-gray-700 dark:text-gray-200 outline-none py-4 pl-4 text-sm rounded" type="email" value="{{ old('email', 'user@dragon.com') }}" placeholder="e.g hello@example.dev" name="email" required autofocus>
                                <span class="absolute top-0 left-0 ml-4 -mt-2 px-1 inline-block bg-transparent text-gray-500 dark:text-gray-300 text-sm">{{ __('crud.inputs.email') }}</span>
                            </div>

                            @csrf

                            <button class="w-full mt-4 inline-block py-4 text-sm text-white font-medium leading-normal bg-red-400 hover:bg-red-300 rounded transition duration-200">{{ __('crud.general.change_password') }}</button>

                            <button @click="toggleForgotPassword" type="button" class="w-full mt-4 inline-block py-4 text-sm text-white font-medium leading-normal bg-indigo-400 hover:bg-indigo-300 rounded transition duration-200">{{ __('crud.general.login') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        let alpineVars = {
            forgotPassword: false,
            toggleForgotPassword: function() {
                this.forgotPassword = !this.forgotPassword;
            },
        }

        function alpineWalletVars() {
            return alpineVars;
        }
    </script>
@endsection