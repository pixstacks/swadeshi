@extends('provider.layout.app')

@section('title')
    Provider {{ __('crud.navlinks.setting') }}
@endsection

@section('heading')
    Provider {{ __('crud.navlinks.setting') }}
@endsection

@push('startScripts')
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="https://js.stripe.com/v3/"></script>
@endpush

@section('content')
    
    {{-- Tabs Starting --}}
    <div class="flex flex-wrap mx-10 mb-6" id="tabs">
        <div class="w-full md:flex inline">
            {{-- tab-head flex mb-0 list-none pb-4 flex-col --}}
            <ul class="tab-head flex mb-0 list-none flex-wrap pt-3 pb-4 flex-row md:flex-col md:flex-nowrap">
                <li class="mr-2 mb-2 last:mr-0 md:flex-none flex-auto text-center">
                    <a class="text-xs font-bold uppercase cursor-pointer px-5 py-3 shadow-lg rounded block leading-normal text-white bg-blue-500" onclick="changeActiveTab(event,'tab-general')" href="#general" id="#general">
                        <i class="fa fa-space-shuttle text-base mr-1"></i> Change Profile
                    </a>
                </li>
                <li class="mr-2 mb-2 last:mr-0 md:flex-none flex-auto text-center">
                    <a class="text-xs font-bold uppercase cursor-pointer px-5 py-3 shadow-lg rounded block leading-normal text-blue-500 bg-white dark:text-gray-300 dark:bg-gray-800" onclick="changeActiveTab(event,'tab-changePassword')" href="#changePassword" id="#changePassword">
                        <i class="fa fa-cog text-base mr-1"></i> Change Password
                    </a>
                </li>
                <li class="mr-2 mb-2 last:mr-0 text-center">
                    <a class="text-xs font-bold uppercase cursor-pointer px-5 py-3 shadow-lg rounded block leading-normal text-blue-500 bg-white dark:text-gray-300 dark:bg-gray-800" onclick="changeActiveTab(event,'tab-addCard')" href="#addCard" id="#addCard">
                        <i class="fa fa-credit-card text-base mr-1"></i> Add Card
                    </a>
                </li>
                <li class="mr-2 mb-2 last:mr-0 md:flex-none flex-auto text-center">
                    <a class="text-xs font-bold uppercase cursor-pointer px-5 py-3 shadow-lg rounded block leading-normal text-blue-500 bg-white dark:text-gray-300 dark:bg-gray-800" onclick="changeActiveTab(event,'tab-verification')" href="#verification" id="#verification">
                        <i class="fa fa-check-circle text-base mr-1"></i> Verified
                    </a>
                </li>
            </ul>
            <div class="relative flex flex-col min-w-0 break-words dark:bg-gray-800 bg-white w-full mb-6 shadow-lg rounded">
                <div class="px-4 py-5 flex-auto">
                    <div class="tab-content tab-space">
                        <div class="block" id="tab-general">
                            <x-form method="post" :action="route('provider.updateProfile')" has-file>
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
                                            <x-inputs.text :label="__('crud.inputs.first_name')" name="first_name" space="w-full" value="{{ auth()->user('provider')->first_name ?? '' }}"></x-inputs.text>
                                            {{-- Last Name --}}
                                            <x-inputs.text :label="__('crud.inputs.last_name')" name="last_name" space="w-full" value="{{ auth()->user('provider')->last_name ?? ''}}"></x-inputs.text>
                                            {{-- Agent Id --}}
                                            <x-inputs.text disabled :label="__('crud.inputs.agent')" name="agent_id" space="w-full" value="{{ auth()->user('provider')->agent_id ?? '' }}"></x-inputs.text>
                                        </div>
                                    </div>
                                    {{-- Email --}}
                                    <x-inputs.email :label="__('crud.inputs.email')" name="email" disabled value="{{ auth()->user('provider')->email ?? '' }}"></x-inputs.email>
                                    {{-- Telephone --}}
                                    <div class="w-full md:w-1/2 px-4 mb-4 md:mb-0">
                                        <div class="mb-6">
                                            <input class="hidden" type="text" id="countryCode" name="country_code" value="{{ auth()->user('provider')->country_code ? auth()->user('provider')->country_code : '91' }}">
                                            <label class="block text-gray-800 text-sm font-semibold mb-2" for="phone">{{ __('crud.inputs.phone') }}</label>
                                            <input type="tel" class="dark:bg-gray-700 dark:text-gray-300 appearance-none w-full p-4 text-xs font-semibold leading-none bg-gray-50 rounded outline-none mb-2 inptFielsd" id="phone" value="{{ auth()->user('provider')->country_code ? '+'.auth()->user('provider')->country_code : '+91' }}{{ auth()->user('provider')->mobile }}" placeholder="123456789" />
                                        </div>
                                    </div>
                                    {{-- Address --}}
                                    <x-inputs.textarea space="w-full" :label="__('crud.inputs.address')" name="address">{{ auth()->user('provider')->address ?? '' }}</x-inputs.textarea>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="right-0 inline-block py-1 px-4 leading-loose bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition duration-200 text-sm" type="submit">{{ __('crud.general.update')." ".__('crud.navlinks.profile') }}</button>
                                </div>
                            </x-form>
                        </div>
                        <div class="hidden" id="tab-changePassword">
                            <x-form method="post" :action="route('provider.changePassword')">
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
                            <form action="{{ route('provider.addCard') }}" method="post" id="myForm">
                                @csrf
                                <div class="flex justify-center items-center">
                                    {{-- <input id="cardholder-name" type="text" name="name"> --}}
                                    <input type="text" name="payment_method_id" id="payment_method_id" value="" style="display: none;">
                                    <x-inputs.text value="" :label="__('crud.payment.name_on_card')" name="name"></x-inputs.text>
                                    <div id="card-element" class="w-full p-4 mb-4 md:mb-0 md:w-1/2 rounded bg-gray-50 dark:bg-gray-700 dark:text-gray-300"></div>
                                    <div id="card-result"></div>
                                </div>
                                <button class="btn bg-green-500 hover:bg-green-600 py-1 px-4 text-white rounded float-right" type="submit" id="card-button">Save Card</button>
                            </form>
                        </div>
                        <div class="hidden" id="tab-verification">
                            @if($notGiven->count())
                                <x-form method="post" :action="route('provider.uploadVerificationDocument')" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach ($notGiven as $req)
                                        <div x-data="fileName()" class="w-full col-span-1 mb-5 dark:bg-gray-700">
                                            <label class="w-full rounded font-bold py-2 px-4 items-center flex justify-between border dark:border-gray-500" for="document{{$loop->index}}">
                                                <span class="text-sm font-medium dark:text-gray-300">{{ $req->name }}</span>
                                                <span class="bg-indigo-500 text-white hover:bg-indigo-dark py-2 px-4 items-center text-xs rounded" x-text="doc">
                                                    
                                                </span>
                                            </label>
                                            <input type="file" class="hidden" id="document{{$loop->index}}" @change="fileChanged" name="document[{{$req->id}}]">
                                        </div>
                                    @endforeach
                                    <div class="col-span-1 md:col-span-2 text-right">
                                        <button type="submit" class="inline-block py-1 px-4 leading-loose bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition duration-200 text-sm" type="submit">{{ __('crud.general.submit') }} {{ __('crud.inputs.documents') }}</button>
                                    </div>
                                </x-form>
                                <br><hr class="dark:border-gray-500"><br>
                            @endif
                            <div>
                                <h2 class="text-center text-3xl dark:text-gray-300 font-semibold">{{ __('crud.inputs.documents') }}</h2>
                                <br>
                                <div class="w-full overflow-hidden rounded-lg shadow-xs mb-6">
                                    <div class="w-full overflow-x-auto">
                                        <table class="w-full whitespace-no-wrap dark:bg-gray-900">
                                            <thead>
                                                <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-900">
                                                    <th class="text-center px-4 py-3">{{ __('crud.inputs.name') }}</th>
                                                    <th class="text-center px-4 py-3">{{ __('crud.inputs.status') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-900">
                                                {{-- Approved --}}
                                                @forelse ($given as $doc)
                                                    <tr class="text-gray-700 dark:text-gray-400">
                                                        <td class="text-center dark:text-gray-400 dark:bg-gray-900 px-4 py-3 text-sm">
                                                            {{ $doc->document->name ?? 'INR' }}
                                                        </td>
                                                        <td class="text-center dark:text-gray-400 dark:bg-gray-900 px-4 py-3 text-sm">
                                                            @if ($doc->status == "ACTIVE")
                                                                <span class="px-2 py-1 font-semibold leading-tight text-green-600 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100 text-xs">
                                                                    Approved
                                                                </span>
                                                            @elseif($doc->status == "ASSESSING")
                                                                <span class="px-2 py-1 font-semibold leading-tight text-yellow-700 bg-yellow-100 rounded-full dark:text-white dark:bg-yellow-600 text-xs">
                                                                    Accessing
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="text-center dark:text-gray-400 dark:bg-gray-900 py-3 text-sm" colspan="6">
                                                            @lang('crud.general.not_found')
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
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
                avatarDataUrl: "{{ auth()->user('provider')->avatar ? asset('storage/'.auth()->user('provider')->avatar) : asset('img/avatar.png') }}",
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

        // Changing Stripe Elements On Mode Change.
        document.addEventListener("darkModeEnabled", () => {
            cardElement.update({
                style: {
                    base: {
                        color: '#d1d5db',
                        fontSize: '10px',
                        '::placeholder': {
                            color: '#9ca3af',
                        },
                        iconColor: '#d1d5db',
                        fontFamily: 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji',
                    },
                    invalid: {
                        iconColor: '#f78286',
                        color: '#f78286',
                    },
                }
            });
        });

        document.addEventListener("darkModeDisabled", () => {
            cardElement.update({
                style: {
                    base: {
                        color: '#1f2937',
                        fontSize: '10px',
                        fontSize: '10px',
                        iconColor: '#9ca3af',
                        fontFamily: 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji',
                    },
                    invalid: {
                        iconColor: '#eb1c26',
                        color: '#eb1c26',
                    },
                }
            });
        });
    </script>
    
@endpush
