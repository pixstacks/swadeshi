@extends('user.layout.app')

@section('title')
    {{ __('crud.general.user') }} {{ __('crud.general.registration') }}
@endsection

@section('content')
    <section class="relative py-20">
        <img class="hidden lg:block absolute top-0 left-0 mt-16 z-10" src="zeus-assets/icons/dots/blue-dot-left-bars.svg" alt="">
        <div class="absolute top-0 left-0 lg:bottom-0 h-128 lg:h-auto w-full lg:w-8/12 bg-cover bg-no-repeat bg-gray-800 opacity-50" @if(array_key_exists('register_user_bg_img', $settings)) style="background-image: url('{{ asset('storage/'.$settings['register_user_bg_img']) }}');" @endif></div>
        <div class="relative container px-4 mx-auto">
            <div class="flex flex-wrap items-center -mx-4">
                <div class="w-full lg:w-1/2 px-4 mb-12 lg:mb-0">
                    <div class="max-w-lg">
                        <h2 class="mb-1 text-4xl dark:text-gray-100 font-semibold font-heading">{{ array_key_exists('register_user_heading', $settings) ? $settings['register_user_heading'] : 'Dummy Heading Here' }}</h2>
                        <p class="text-xl dark:text-gray-200 text-gray-800">{{ array_key_exists('register_user_desc', $settings) ? $settings['register_user_desc'] : '' }}</p>
                    </div>
                </div>
                <div class="w-full lg:w-1/2 px-4">
                    <div class="lg:max-w-lg p-6 lg:px-10 lg:py-12 bg-white dark:bg-gray-900 dark:border-gray-800 text-center border rounded-xl">
                        <form action="{{ route('user.register') }}" method="post">
                            <span class="inline-block mb-4 text-xs text-blue-400 dark:text-gray-400  font-semibold">Sign Up</span>
                            <h3 class="mb-12 text-3xl font-semibold font-heading dark:text-blue-100">Create new account</h3>
                            <div class="grid md:grid-cols-2 grid-cols-1 gap-1">
                                <div class="relative col-span-1 flex flex-wrap mb-6">
                                    <input class="bg-gray-50 outline-none dark:bg-gray-700 dark:text-gray-200 relative mb-2 md:mb-0 w-full py-4 pl-4 text-sm rounded" type="text" placeholder="e.g John" name="first_name" value="{{ old('first_name', '')}}">
                                    <span class="absolute top-0 left-0 ml-4 -mt-2 px-1 inline-block bg-transparent text-gray-500 dark:text-gray-300 text-sm">First Name</span>
                                </div>
                                <div class="col-span-1 relative flex flex-wrap mb-6">
                                    <input class="bg-gray-50 outline-none dark:bg-gray-700 dark:text-gray-200 relative mb-2 md:mb-0 w-full py-4 pl-4 text-sm rounded" type="text" placeholder="e.g Doe" name="last_name" value="{{ old('last_name', '') }}">
                                    <span class="absolute top-0 left-0 ml-4 -mt-2 px-1 inline-block bg-transparent text-gray-500 dark:text-gray-300 text-sm">Last Name</span>
                                </div>
                            </div>
                            <div class="relative flex flex-wrap mb-6">
                                <input class="bg-gray-50 outline-none dark:bg-gray-700 dark:text-gray-200 relative mb-2 md:mb-0 w-full py-4 pl-4 text-sm rounded" type="email" placeholder="e.g your@email.com" value="{{ old('email', '') }}" name="email">
                                <span class="absolute top-0 left-0 ml-4 -mt-2 px-1 inline-block bg-transparent text-gray-500 dark:text-gray-300 text-sm">Your email address</span>
                            </div>
                            {{-- Gender --}}
                            <div class="relative flex flex-wrap mb-6">
                                <select class="bg-gray-50 outline-none dark:bg-gray-700 dark:text-gray-200 relative mb-2 md:mb-0 w-full py-4 pl-4 text-sm rounded"  name="gender" id="">
                                    <option {{ old('gender', '') == 'MALE' ?? 'selected' }} value="MALE">Male</option>
                                    <option {{ old('gender', '') == 'FEMALE' ?? 'selected' }} value="FEMALE">Female</option>
                                </select>
                                <span class="absolute top-0 left-0 ml-4 -mt-2 px-1 inline-block bg-transparent text-gray-500 dark:text-gray-300 text-sm">{{ __('crud.inputs.gender') }}</span>
                            </div>
                            {{-- Telephone --}}
                            <div class="relative flex flex-wrap mb-6">
                                <input type="tel" class="dark:bg-gray-700 dark:text-gray-300 appearance-none w-full p-4 text-xs font-semibold leading-none bg-gray-50 rounded outline-none mb-2 inptFielsd" id="phone" value="{{ old('mobile', '') }}" placeholder="123456789" />
                                <input type="number" hidden value="{{ old('country_code', '91') }}" name="country_code" id="countryCode">
                                <span class="absolute top-0 left-0 ml-4 -mt-2 px-1 inline-block bg-transparent text-gray-500 dark:text-gray-300 text-sm">{{ __('crud.inputs.phone') }}</span>
                            </div>

                            <div class="relative flex flex-wrap mb-6">
                                <input class="bg-gray-50 outline-none dark:bg-gray-700 dark:text-gray-200 relative mb-2 md:mb-0 w-full py-4 pl-4 text-sm rounded" type="password" placeholder="******" name="password" name="password_confirmation">
                                <span class="absolute top-0 left-0 ml-4 -mt-2 px-1 inline-block bg-transparent text-gray-500 dark:text-gray-300 text-sm">Password</span>
                            </div>
                            
                            <div class="relative flex flex-wrap mb-6">
                                <input class="bg-gray-50 outline-none dark:bg-gray-700 dark:text-gray-200 relative mb-2 md:mb-0 w-full py-4 pl-4 text-sm rounded" type="password" placeholder="******" name="password_confirmation">
                                <span class="absolute top-0 left-0 ml-4 -mt-2 px-1 inline-block bg-transparent text-gray-500 dark:text-gray-300 text-sm">Repeat password</span>
                            </div>
                            @csrf
                            <label class="inline-flex mb-10 text-left">
                                <input class="mr-2" type="checkbox" name="terms" checked>
                                <span class="-mt-1 inline-block text-sm text-gray-500">By signing up, you agree to our <a class="text-red-400 hover:underline" href="#">Terms, Data Policy</a> and <a class="text-red-400 hover:underline" href="#">Cookies Policy</a>.</span>
                            </label>
                            <button class="w-full inline-block py-4 text-sm text-white font-medium leading-normal bg-red-400 hover:bg-red-300 rounded transition duration-200">Get Started</button>
                            <a href="{{ route('user.google.registration') }}">Register with Google.</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        @push('endScripts')
            <style>
                .iti__selected-flag:focus {
                    outline: 0px;
                }
                .iti {
                    width: 100%;
                }
                .iti--separate-dial-code .iti__selected-flag {
                    background: transparent !important;
                }
            </style>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js" integrity="sha512-DNeDhsl+FWnx5B1EQzsayHMyP6Xl/Mg+vcnFPXGNjUZrW28hQaa1+A4qL9M+AiOMmkAhKAWYHh1a+t6qxthzUw==" crossorigin="anonymous"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css" integrity="sha512-yye/u0ehQsrVrfSd6biT17t39Rg9kNc+vENcCXZuMz2a+LWFGvXUnYuWUW6pbfYj1jcBb/C39UZw2ciQvwDDvg==" crossorigin="anonymous" />
            <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js" integrity="sha512-BNZ1x39RMH+UYylOW419beaGO0wqdSkO7pi1rYDYco9OL3uvXaC/GTqA5O4CVK2j4K9ZkoDNSSHVkEQKkgwdiw==" crossorigin="anonymous"></script>
            <!-- JAVASCRIPT CODE REQUIRED -->
            <script>
                let input = document.querySelector("#phone");
                let countryCode = document.querySelector('#countryCode');
                
                let iti = window.intlTelInput(input, {
                    separateDialCode: true,
                    initialCountry: "in",
                    hiddenInput: "mobile",
                });

                input.addEventListener('countrychange', function(e) {
                    countryCode.value = parseInt(iti.getSelectedCountryData().dialCode);
                    console.log(parseInt(iti.getSelectedCountryData().dialCode));
                });
            </script>
        @endpush
    </section>
@endsection