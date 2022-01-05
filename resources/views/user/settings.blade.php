@extends('user.layout.app')

@section('title')
    User {{ __('crud.navlinks.setting') }}
@endsection

@push('startScripts')
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="https://js.stripe.com/v3/"></script>
@endpush

@section('content')
    
    {{-- Tabs Starting --}}
    <div class="container px-6 mx-auto flex flex-wrap mb-6" id="tabs">
        <div class="w-full flex">
            <ul class="tab-head flex mb-0 list-none pb-4 flex-col">
                <li class="mr-2 mb-2 last:mr-0 text-center">
                    <a class="text-xs font-bold uppercase cursor-pointer px-5 py-3 shadow-lg rounded block leading-normal text-white bg-blue-500" onclick="changeActiveTab(event,'tab-general')" href="#general" id="#general">
                        <i class="fa fa-space-shuttle text-base mr-1"></i> Change Profile
                    </a>
                </li>
                <li class="mr-2 mb-2 last:mr-0 text-center">
                    <a class="text-xs font-bold uppercase cursor-pointer px-5 py-3 shadow-lg rounded block leading-normal text-blue-500 bg-white dark:text-gray-300 dark:bg-gray-800" onclick="changeActiveTab(event,'tab-changePassword')" href="#changePassword" id="#changePassword">
                        <i class="fa fa-cog text-base mr-1"></i> Change Password
                    </a>
                </li>
                <li class="mr-2 mb-2 last:mr-0 text-center">
                    <a class="text-xs font-bold uppercase cursor-pointer px-5 py-3 shadow-lg rounded block leading-normal text-blue-500 bg-white dark:text-gray-300 dark:bg-gray-800" onclick="changeActiveTab(event,'tab-addCard')" href="#addCard" id="#addCard">
                        <i class="fa fa-credit-card text-base mr-1"></i> Add Card
                    </a>
                </li>
            </ul>
            <div class="relative flex flex-col min-w-0 break-words dark:bg-gray-800 bg-white w-full mb-6 shadow-lg rounded border-gray-100 dark:border-gray-700 border">
                <div class="px-4 py-5 flex-auto">
                    <div class="tab-content tab-space">
                        <div class="block" id="tab-general">
                            <x-form method="post" :action="route('user.updateProfile')" has-file>
                                <div class="flex flex-wrap -mx-4 -mb-4 md:mb-0">
                                    <div class="w-full md:w-1/2 mb-4 md:mb-0">
                                        <div class="h-full flex items-center justify-center relative" x-data="avatarComponentData()">
                                            <img class="rounded-full" :src="avatarDataUrl" style="object-fit: cover; width: 150px; height: 150px;"/>
                                            <label for="avatar" title="Select New Avatar">
                                                <i class="fa rounded-full text-white bg-blue-600 absolute fa-pencil cursor-pointer" style="font-size: 12px; padding: 8px; top: 10%; right: 20%"></i>
                                            </label>
                                            <input type="file" class="hidden" name="avatar" id="avatar" @change="fileChanged" />
                                        </div>
                                    </div>
                                    <div class="w-full md:w-1/2 mb-4 md:mb-0">
                                        <div class="mb-6">
                                            {{-- First Name --}}
                                            <x-inputs.text :label="__('crud.inputs.first_name')" name="first_name" space="w-full" value="{{ auth()->user('web')->first_name ?? '' }}"></x-inputs.text>
                                            {{-- Last Name --}}
                                            <x-inputs.text :label="__('crud.inputs.last_name')" name="last_name" space="w-full" value="{{ auth()->user('web')->last_name ?? ''}}"></x-inputs.text>
                                        </div>
                                    </div>
                                    {{-- Email --}}
                                    <x-inputs.email :label="__('crud.inputs.email')" name="email" disabled value="{{ auth()->user('web')->email ?? '' }}"></x-inputs.email>
                                    {{-- Telephone --}}
                                    <div class="w-full md:w-1/2 px-4 mb-4 md:mb-0">
                                        <div class="mb-6">
                                            <input class="hidden" type="text" id="countryCode" name="country_code" value="{{ auth()->user('web')->country_code ? auth()->user('web')->country_code : '91' }}">
                                            <label class="block text-gray-800 text-sm font-semibold mb-2" for="phone">{{ __('crud.inputs.phone') }}</label>
                                            <input type="tel" class="dark:bg-gray-700 dark:text-gray-300 appearance-none w-full p-4 text-xs font-semibold leading-none bg-gray-50 rounded outline-none mb-2 inptFielsd" id="phone" value="{{ auth()->user('web')->country_code ? '+'.auth()->user('web')->country_code : '+91' }}{{ auth()->user('web')->mobile }}" placeholder="123456789" />
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="right-0 inline-block py-1 px-4 leading-loose bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition duration-200 text-sm" type="submit">{{ __('crud.general.update')." ".__('crud.navlinks.profile') }}</button>
                                </div>
                            </x-form>
                        </div>
                        <div class="hidden" id="tab-changePassword">
                            <x-form method="post" :action="route('user.changePassword')">
                                <div class="flex flex-wrap -mx-4 -mb-4 md:mb-0">
                                    {{-- Old Password --}}
                                    <x-inputs.password :label="__('crud.inputs.old_password')" name="old_password" space="md:w-full"></x-inputs.password>
                                    {{-- New Password --}}
                                    <x-inputs.password :label="__('crud.inputs.new_password')" name="password"></x-inputs.password>
                                    {{-- Confirm New Password --}}
                                    <x-inputs.password :label="__('crud.inputs.password_confirmation')" name="password_confirmation"></x-inputs.password>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="right-0 inline-block py-1 px-4 leading-loose bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition duration-200 text-sm" type="submit">{{ __('crud.general.change_password') }}</button>
                                </div>
                            </x-form>
                        </div>
                        <div class="hidden" id="tab-addCard">
                            <form action="{{ route('user.addCard') }}" method="post" id="myForm">
                                @csrf
                                <div class="flex justify-center items-center">
                                    {{-- <input id="cardholder-name" type="text" name="name"> --}}
                                    <input type="text" name="payment_method_id" id="payment_method_id" value="" style="display: none;">
                                    <x-inputs.text value="" :label="__('crud.payment.name_on_card')" name="name"></x-inputs.text>
                                    <div id="card-element" class="w-full p-4 mb-4 md:mb-0 md:w-1/2 rounded" style="background-color: #f9fafb"></div>
                                    <div id="card-result"></div>
                                </div>
                                <button class="btn bg-green-500 hover:bg-green-600 py-1 px-4 text-white rounded float-right" type="submit" id="card-button">Save Card</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('components.telephoneImport')

@push('endScripts')
    <script>
        function fileName()
        {
            return {
                doc: 'Select File',
                fileChanged(event) {
                    this.fileToDataUrl(event)
                },
                fileToDataUrl(event) {
                    if (! event.target.files.length) return
                    let file = event.target.files[0];
                    this.doc = file.name;
                }
            }
        }
    </script>
    <script>
        /* Alpine component for avatar uploader viewer */
        function avatarComponentData() {
            return {
                avatarDataUrl: "{{ auth()->user('web')->avatar ? asset('storage/'.auth()->user('web')->avatar) : asset('img/avatar.png') }}",
                fileChanged(event) {
                    this.fileToDataUrl(event, src => this.avatarDataUrl = src)
                },
                fileToDataUrl(event, callback) {
                    if (! event.target.files.length) return
                    let file = event.target.files[0],
                        reader = new FileReader()
                    reader.readAsDataURL(file)
                    reader.onload = e => callback(e.target.result)
                }
            }
        }
    </script>

    {{-- Adding Card Script --}}
    <script>
        var stripe = Stripe('{{ config('constants.stripe_publishable_key') }}');

        var elements = stripe.elements();
        var cardElement = elements.create('card');
        cardElement.mount('#card-element');

        var cardholderName = document.getElementById('name');
        var cardButton = document.getElementById('card-button');
        var resultContainer = document.getElementById('card-result');

        document.getElementById("myForm").addEventListener("submit", saveCard);

        function saveCard(e) 
        {
            event.preventDefault();

            stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                    billing_details: {
                        name: cardholderName.value,
                    },
                }
            ).then(function(result) {
                if (result.error) {
                    // Display error.message in your UI
                    Livewire.emit('livewire_error', result.error.message);
                    return false;
                } else {
                    // You have successfully created a new PaymentMethod
                    document.getElementById('payment_method_id').value = result.paymentMethod.id;
                    document.getElementById('myForm').submit();
                }
            });
        }

    </script>
@endpush
