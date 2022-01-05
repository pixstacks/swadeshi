@extends('user.layout.app')

@section('title')
    {{ __('crud.general.wallet') }}
@endsection

@section('content')
    @push('endScripts')
        <script>

            function sleep(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }

            function validateForm() {
                card_id = document.getElementById('card_id').value;
                if(!alpineVars.newCard && (card_id == '' || card_id == null)) {
                    notyf.error("Payment Method Not Selected");
                    return false;
                }
                return true;
            }

            let alpineVars = {
                showWallet: true,
                showCard: false,
                newCard: false,
                // openCVC: null,
                toggleCVC: function(value, id) {
                    // this.openCVC = value;
                    document.getElementById('card_id').value = id;
                    var all = document.getElementsByClassName('cardElement');
                    var i = 0;
                    for(i = 0; i < all.length; i++) {
                        all[i].classList.remove("border-yellow-300");
                        all[i].classList.remove("bg-yellow-100");
                        all[i].classList.remove("dark:text-gray-600");
                    }
                    document.getElementById('element'+value).classList.add("border-yellow-300");
                    document.getElementById('element'+value).classList.add("bg-yellow-100");
                    document.getElementById('element'+value).classList.add("dark:text-gray-600");
                },
                toggleWallet: function() {
                    this.showWallet = !this.showWallet;
                    this.showCard = false;
                    this.openCVC = null;
                },
                payNewCard: function() {
                    this.newCard = true;
                },
                payOldCard: function() {
                    this.newCard = false;
                },
                toggleCard: function() {
                    if(!this.showCard) {
                        var amount = document.getElementById("amount").value;
                        if(amount == '' || amount == null) {
                            notyf.error("Set Amount Before Selecting Payment Method");
                            return 0;
                        }
                        if(amount < 10) {
                            notyf.error("Amount must be atleast {{ currency(10) }}");
                            return 0;
                        }
                    }
                    this.showCard = !this.showCard;
                }
            }

            function alpineWalletVars() {
                return alpineVars;
            }
        </script>
    @endpush
    <section x-data="alpineWalletVars()" x-cloak>
        <div class="w-full md:flex mb-4">
            <div class="w-full md:w-1/2 flex justify-center items-center">
                <img style="height: 350px;" src="{{ asset('img/cards/wallet.jpg') }}" alt="">
            </div>
            <div class="w-full md:w-1/2 flex flex-col md:pr-8 p-4 items-center md:items-start relative">
                <div class="flex items-center w-full text-center h-20 justify-end md:pr-8">
                    <span x-on:click="toggleWallet" class="rounded cursor-pointer border shadow-xl bg-white dark:bg-gray-800 dark:text-gray-300 p-3 px-4" x-show.transition.in="showWallet">Add To Wallet</span>
                    <span x-on:click="toggleWallet" class="rounded cursor-pointer border shadow-xl bg-white dark:bg-gray-800 dark:text-gray-300 p-3 px-4" x-show.transition.in="!showWallet">Show Wallet</span>
                </div>
                <div class="flex justify-center items-center w-full" style="height: -webkit-fill-available">
                    <div x-show.transition.in="showWallet"
                        class="w-full flex flex-col md:pr-8 p-4 justify-center items-center md:items-start"
                    >
                        <div class="w-full md:pt-0 pt-5 dark:text-gray-200">
                            <span class="text-3xl w-full tracking-tight">
                                Current Balance:
                            </span>
                            <hr>
                            <span class="text-xl">
                                {{ currency(auth()->user('web')->wallet_balance) }}
                            </span>
                        </div>
                    </div>
                    <div x-show.transition.in="!showWallet"
                        class="w-full flex flex-col md:pr-8 p-4 justify-center items-center md:items-start"
                    >
                        <div class="w-full">
                            <x-form method="post" :action="route('user.addToWallet')" onkeydown="return event.key != 'Enter';" onsubmit="return validateForm()">
                                <div x-show.transition.in="!showCard">
                                    <x-inputs.number space="w-full" value="" :label="__('crud.inputs.amount')" name="amount" required @keyup.enter="toggleCard()"></x-inputs.number>
                                    <div class="flex justify-end">
                                        <button type="button" class="text-right mx-4 bg-blue-500 hover:bg-blue-600 text-white py-2 px-3 rounded" @click="toggleCard()">Select Card</button>
                                    </div>
                                </div>
                                <div x-show.transition.in="showCard">
                                    <div x-show.transition.in="!newCard">
                                        @forelse ($cards ?? [] as $card)
                                            @php
                                                $pm_id = $card->id;
                                                $billingDetails = $card->billing_details;
                                                $card = $card->card;
                                            @endphp
                                            <div class="cardElement border cursor-pointer w-full mb-4 rounded dark:text-gray-300" id="element{{$loop->index}}">
                                                <div class="p-4" @click="toggleCVC({{$loop->index}},'{{$pm_id}}')">
                                                    <div class="flex justify-between w-full items-center">
                                                        <div class="flex items-center">
                                                            @if(in_array($card->brand, ['amex', 'mastercard', 'visa']))
                                                                <img class="inline" src="{{ asset('img/cards/'.$card->brand.'.png') }}" alt="NotFound">
                                                            @endif
                                                            <span class="inline ml-2">
                                                                **** **** **** {{ $card->last4 }}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            Expires On: @if($card->exp_month<10){{'0'}}@endif{{$card->exp_month."/".$card->exp_year}}
                                                        </div>
                                                    </div>
                                                    <div class="my-2">
                                                        Name On Card: {{ $billingDetails->name }}
                                                    </div>
                                                    {{-- <template x-if.transition.in="openCVC=={{$loop->index}}">
                                                        Enter CVC: <input class="appearance-none w-32 p-2 text-xs font-semibold leading-none rounded outline-none border bg-gray-50 dark:bg-gray-700 dark:text-gray-300" type="number" name="cvc" min="000">
                                                    </template> --}}
                                                </div>
                                            </div>
                                        @empty
                                            <div class="mb-5 w-full shadow p-4 dark:text-gray-300">
                                                No Saved Cards
                                            </div>
                                        @endforelse
                                        <input type="text" id="card_id" class="hidden" name="card_id" >
                                        <button type="button" class="text-right bg-blue-500 hover:bg-blue-600 text-white py-2 px-3 rounded" @click="payNewCard()">Pay Using New Card</button>
                                        <button type="button" class="text-right bg-blue-500 hover:bg-blue-600 text-white py-2 px-3 rounded" @click="toggleCard()">Change Amount</button>
                                        <button type="submit" class="float-right bg-blue-500 hover:bg-blue-600 text-white py-2 px-3 rounded">Add Money</button>
                                    </div>
                                    <div x-show.transition.in="newCard">
                                        <label class="inline-flex items-center my-3">
                                            <input type="checkbox" class="form-checkbox h-5 w-5 text-purple-600" name="saveCard" checked><span class="ml-2 text-gray-700 dark:text-gray-300">Save This Card</span>
                                        </label>
                                        <br>
                                        <button type="button" class="text-right bg-blue-500 hover:bg-blue-600 text-white py-2 px-3 rounded" @click="payOldCard()">Pay Using Old Card</button>
                                        <button type="button" class="text-right bg-blue-500 hover:bg-blue-600 text-white py-2 px-3 rounded" @click="toggleCard()">Change Amount</button>
                                        <button type="submit" class="float-right bg-blue-500 hover:bg-blue-600 text-white py-2 px-3 rounded">Add Money</button>
                                    </div>
                                </div>
                            </x-form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if(!empty($transactions))

        <hr class="mb-10">

        <h2 class="my-6 text-2xl font-semibold text-gray-700 text-center dark:text-gray-300">
            Transaction History
        </h2>

        <div class="w-4/5 mx-auto overflow-hidden rounded-lg shadow-xs mb-6">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr
                            class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                            <th class="text-center px-4 py-3">{{ __('crud.inputs.SNo') }}</th>
                            <th class="text-center px-4 py-3">{{ __('crud.payment.transaction_type') }}</th>
                            <th class="text-center px-4 py-3">{{ __('crud.inputs.amount') }}</th>
                            <th class="text-center px-4 py-3">{{ __('crud.inputs.description') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 overflow-y-scroll">
                        {{-- Approved --}}
                        @forelse ($transactions as $transaction)
                        <tr class="text-gray-700 dark:text-gray-400">
                            <td class="text-center dark:text-gray-400 dark:bg-gray-800 px-4 py-3">{{ $loop->index + 1 }}</td>
                            <td class="text-center dark:text-gray-400 dark:bg-gray-800 px-4 py-3">{{ $transaction->type == 'C' ? 'Credit' : 'Debit' }}</td>
                            <td class="px-4 dark:text-gray-400 dark:bg-gray-800 py-3 text-center">
                                {{ currency($transaction->amount) }}
                            </td>
                            <td class="px-4 dark:text-gray-400 dark:bg-gray-800 py-3 text-sm text-center">
                                {{ $transaction->transaction_desc ? ucfirst($transaction->transaction_desc) : ''}}
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td class="text-center dark:text-gray-400 dark:bg-gray-800 py-3 text-sm" colspan="10">
                                    @lang('crud.general.not_found')
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div class="">
                {!! $transactions->links() !!}
            </div>
        </div>
    @endif
    @if(!empty($response))
        @php
            session()->reflash();
        @endphp
        @push('endScripts')
            <script src="https://js.stripe.com/v3/"></script>
            <script>
                var stripe = Stripe('{{ config('constants.stripe_publishable_key') }}');
                response = JSON.parse('@php echo $response @endphp');
                var intent = "{{ session('intentId') }}";
                handleServerResponse(response);

                function handleServerResponse(responseJson) {
                    try {
                        stripe.retrievePaymentIntent(response.clientSecret)
                        .then(function(result) {
                            if (result.error) {
                                location.replace("{{ route('user.wallet') }}");
                            }
                            else {
                                if(result.paymentIntent.status == 'succeeded' || result.paymentIntent.status == 'canceled') {
                                    location.replace("{{ route('user.wallet') }}");
                                }
                                else {
                                    if (responseJson.requiresAction) {
                                        // Card Authentication
                                        stripe.handleCardAction(
                                            responseJson.clientSecret
                                        ).then(function(result) {
                                            if (result.error) {
                                                location.replace("{{ route('user.stripe.paymentFailed', session('intentId')) }}");
                                            } else {
                                                location.replace("{{ route('user.stripe.paymentSuccessful', session('intentId')) }}");
                                            }
                                        });
                                    }
                                }
                            }
                        });
                    } catch(e) {
                        location.replace("{{ route('user.wallet') }}");
                    }
                }
            </script>
            @unset($response)
        @endpush
    @endif
@endsection