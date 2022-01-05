@extends('admin.layout.app')

@section('title')
Admin - Payment Settings
@endsection

@section('heading')
Payment Settings
@endsection

@section('content')
<div class="w-full rounded-lg shadow-xs bg-white dark:text-gray-400 dark:bg-gray-800 mb-5">
    <div class="w-full px-5 py-5">
        <x-form action="{{ route('admin.payment.saveSetting') }}" method="post" has-file>
            <div class="flex flex-wrap -mx-4 -mb-4 md:mb-0">
                <x-inputs.number min="0" :label="__('crud.payment.daily_target')" name="payment_daily_target"
                    value="{{ config('constants.payment_daily_target', '') }}"></x-inputs.number>

                <x-inputs.number min="0" label="{{__('crud.payment.tax_percent')}} (%)" name="tax_percentage"
                    value="{{ config('constants.tax_percentage', '') }}"></x-inputs.number>

                <x-inputs.number min="0" label="{{__('crud.inputs.commission')}} (%)" name="commission_percentage"
                    value="{{ config('constants.commission_percentage', '') }}"></x-inputs.number>

                <x-inputs.number min="0"
                    label="{{__('crud.admin.providers.name')}} {{__('crud.inputs.commission')}} (%)"
                    name="provider_commission_percentage"
                    value="{{ config('constants.provider_commission_percentage', '') }}"></x-inputs.number>

                <x-inputs.number min="0"
                    label="{{__('crud.admin.peakHours.name')}} {{__('crud.inputs.commission')}} (%)"
                    name="payment_peakHours_commission"
                    value="{{ config('constants.payment_peakHours_commission', '') }}"></x-inputs.number>

                <x-inputs.number max="0" :label="__('crud.payment.mini_negative_bal')" name="payment_mini_negative_bal"
                    value="{{ config('constants.payment_mini_negative_bal', '') }}"></x-inputs.number>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="right-0 inline-block py-1 px-4 leading-loose bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition duration-200 text-sm"
                    type="submit">{{ __('crud.general.update') }}</button>
            </div>
        </x-form>
    </div>
</div>
<div class="w-full rounded-lg shadow-xs bg-white dark:text-gray-400 dark:bg-gray-800 mb-5">
    <div class="w-full px-5 py-5">
        <x-form action="{{ route('admin.payment.savePaymentMethodSetting') }}" method="post" has-file>
            <div class="grid grid-wrap grid-cols-2 -mx-4 -mb-4 md:mb-0">


                                {{-- Cash Payment --}}
                                {{-- <x-inputs.slide-checkbox :label="__('crud.admin.settings.cash_payment')" :value="config('constants.cash_payment')" name="cash_payment"
                                :status="config('constants.cash_payment')" :checked="config('constants.online_payment') == '1' ? true : false">
                            </x-inputs.slide-checkbox>
            
                            <x-inputs.slide-checkbox :label="__('crud.admin.settings.online_payment')" :value="config('constants.online_payment')" name="online_payment"
                            :status="config('constants.online_payment')" :checked="config('constants.online_payment') == '1' ? true : false">
                        </x-inputs.slide-checkbox>
            
                            {{-- Stripe Payment --}}
                            {{-- <div x-data="{ stripe_payment : '{{config('constants.stripe_payment')}}' }">
                                <x-inputs.slide-checkbox :label="__('crud.inputs.stripe_payment')" :value="config('constants.stripe_payment')" name="stripe_payment"
                                :status="config('constants.stripe_payment')" :checked="config('constants.stripe_payment') == '1' ? true : false" @click="stripe_payment === '0' ? stripe_payment = '1' : stripe_payment = '0'">
                            </x-inputs.slide-checkbox>
                                            
                                <div x-show="stripe_payment === '1'">
                                    <x-inputs.text space="md:w-1/2" :label="__('crud.inputs.stripe_publishable_key')"
                                        name="stripe_publishable_key"
                                        value="{{ config('constants.stripe_publishable_key') ?? '' }}">
                                    </x-inputs.text>
            
                                    <x-inputs.password space="md:w-1/2" :label="__('crud.inputs.stripe_secret_key')"
                                        name="stripe_secret_key" value="{{ config('constants.stripe_secret_key') ?? '' }}">
                                    </x-inputs.password>
                                </div>
                            </div> --}} 

                {{-- Cash Payment --}}
                {{-- <x-inputs.status :label="__('crud.admin.settings.cash_payment')" name="cash_payment"
                    :status="config('constants.cash_payment')">
                </x-inputs.status> --}}
                <div class="p-4 m-2 font-semibold  rounded outline-none dark:bg-gray-700 dark:text-gray-300"
                    x-data="{ cash_payment: '{{config('constants.cash_payment')}}' }">
                    <label class=" dark:text-gray-400 block text-gray-800 text-sm font-semibold mb-2">
                        {{ __('crud.admin.settings.cash_payment') }}
                    </label>
                    <input type="hidden" name="cash_payment" x-bind:value="cash_payment">
                    <div class="relative rounded-full w-12 h-6 transition duration-200 ease-linear"
                        :class="[cash_payment === '1' ? 'bg-green-400' : 'bg-gray-400']">
                        <label for="cash_payment"
                            class="absolute left-0 bg-white border-2 mb-2 w-6 h-6 rounded-full transition transform duration-100 ease-linear cursor-pointer"
                            :class="[cash_payment == '1' ? 'translate-x-full border-green-400' : 'translate-x-0 border-gray-400']"></label>
                        <input type="checkbox" id="cash_payment" name="cash_payment" x-bind:value="cash_payment"
                            class="appearance-none w-full h-full active:outline-none focus:outline-none"
                            @click="cash_payment === '0' ? cash_payment = '1' : cash_payment = '0'">
                    </div>
                </div>
                {{-- Online Payment --}}
                {{-- <x-inputs.status :label="__('crud.admin.settings.online_payment')" name="online_payment"
                    :status="config('constants.online_payment')">
                </x-inputs.status> --}}
                <div class="p-4 m-2 font-semibold  rounded outline-none dark:bg-gray-700 dark:text-gray-300"
                    x-data="{ online_payment: '{{config('constants.online_payment')}}' }">
                    <label class=" dark:text-gray-400 block text-gray-800 text-sm font-semibold mb-2">
                        {{ __('crud.admin.settings.online_payment') }}
                    </label>
                    <input type="hidden" name="online_payment" x-bind:value="online_payment">
                    <div class="relative rounded-full w-12 h-6 transition duration-200 ease-linear"
                        :class="[online_payment == '1' ? 'bg-green-400' : 'bg-gray-400']">
                        <label for="online_payment"
                            class="absolute left-0 bg-white border-2 mb-2 w-6 h-6 rounded-full transition transform duration-100 ease-linear cursor-pointer"
                            :class="[online_payment == '1' ? 'translate-x-full border-green-400' : 'translate-x-0 border-gray-400']"></label>
                        <input type="checkbox" id="online_payment" name="online_payment" x-bind:value="online_payment"
                            class="appearance-none w-full h-full active:outline-none focus:outline-none"
                            @click="toggleOnlineToggle"
                            @click="online_payment === '0' ? online_payment = '1' : online_payment = '0'">
                    </div>
                </div>
                {{-- Stripe Payment --}}
                <div x-data="{ stripe_payment : '{{config('constants.stripe_payment')}}' }">
                    <div class=" p-4 m-2 font-semibold  rounded outline-none dark:bg-gray-700 dark:text-gray-300">
                        <label class=" dark:text-gray-400 block text-gray-800 text-sm font-semibold mb-2">
                            {{ __('crud.inputs.stripe_payment') }}
                        </label>
                        <input type="hidden" name="stripe_payment " x-bind:value="stripe_payment">
                        <div class="relative rounded-full w-12 h-6 transition duration-200 ease-linear "
                            :class="[stripe_payment === '1' ? 'bg-green-400' : 'bg-gray-400']">
                            <label for="stripe_payment"
                                class="absolute left-0 bg-white border-2 mb-2 w-6 h-6 rounded-full transition transform duration-100 ease-linear cursor-pointer"
                                :class="[stripe_payment == '1' ? 'translate-x-full border-green-400' : 'translate-x-0 border-gray-400']"></label>
                            <input type="checkbox" id="stripe_payment" name="stripe_payment"
                                x-bind:value="stripe_payment"
                                class="appearance-none w-full h-full active:outline-none focus:outline-none"
                                @click="toggleStripeToggle"
                                @click="stripe_payment === '0' ? stripe_payment = '1' : stripe_payment = '0'">

                            {{-- <x-inputs.status :label="__('crud.inputs.stripe_payment')" name="stripe_payment"
                    :status="config('constants.stripe_payment')" @click="show = !show">
                </x-inputs.status> --}}
                        </div>
                    </div>
                    <template x-if="isStripeOpen">
                    <div x-show="stripe_payment === '1'">
                        <x-inputs.text space="md:w-1/2" :label="__('crud.inputs.stripe_publishable_key')"
                            name="stripe_publishable_key"
                            value="{{ config('constants.stripe_publishable_key') ?? '' }}">
                        </x-inputs.text>

                        <x-inputs.password space="md:w-1/2" :label="__('crud.inputs.stripe_secret_key')"
                            name="stripe_secret_key" value="{{ config('constants.stripe_secret_key') ?? '' }}">
                        </x-inputs.password>
                    </div>
                    </template>
                </div>
                <x-inputs.select :label="__('crud.admin.settings.currency')" name="currency" space="md:w-1/2">
                    <option @if(config('constants.currency')=="RM" ) selected @endif value="RM">
                        Malaysian ringgit (RM)</option>
                    <option @if(config('constants.currency')=="R" ) selected @endif value="R">
                        South African rand (ZAR)</option>
                    <option @if(config('constants.currency')=="₦" ) selected @endif value="₦">
                        Nigerian naira (NGN)</option>
                    <option @if(config('constants.currency')=="$" ) selected @endif value="$">US
                        Dollar (USD)</option>
                    <option @if(config('constants.currency')=="₹" ) selected @endif value="₹">
                        Indian Rupee (INR)</option>
                    <option @if(config('constants.currency')=="د.ك" ) selected @endif value="د.ك">
                        Kuwaiti Dinar (KWD)</option>
                    <option @if(config('constants.currency')=="د.ب" ) selected @endif value="د.ب">
                        Bahraini Dinar (BHD)</option>
                    <option @if(config('constants.currency')=="﷼" ) selected @endif value="﷼">
                        Omani Rial (OMR)</option>
                    <option @if(config('constants.currency')=="£" ) selected @endif value="£">
                        British Pound (GBP)</option>
                    <option @if(config('constants.currency')=="€" ) selected @endif value="€">
                        Euro (EUR)</option>
                    <option @if(config('constants.currency')=="CHF" ) selected @endif value="CHF">Swiss
                        Franc (CHF)</option>
                    <option @if(config('constants.currency')=="ل.د" ) selected @endif value="ل.د">Libyan
                        Dinar (LYD)</option>
                    <option @if(config('constants.currency')=="B$" ) selected @endif value="B$">
                        Bruneian Dollar (BND)</option>
                    <option @if(config('constants.currency')=="S$" ) selected @endif value="S$">
                        Singapore Dollar (SGD)</option>
                    <option @if(config('constants.currency')=="C$" ) selected @endif value="C$">
                        Canadian dollar (CAD)</option>
                    <option @if(config('constants.currency')=="AU$" ) selected @endif value="AU$">
                        Australian Dollar (AUD)</option>
                </x-inputs.select>

                <div class="w-full px-4 mb-4 md:mb-0">
                    <div class="mb-6">
                        <div class="flex justify-end">
                            <button type="submit"
                                class="right-0 inline-block py-1 px-4 leading-loose bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition duration-200 text-sm"
                                type="submit">{{ __('crud.general.update') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>
@endsection