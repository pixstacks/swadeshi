<div class="mx-auto w-11/12 flex-wrap grid grid-cols-2 gap-4" x-data="{'showRequestDetails':@entangle('showRequestDetails')}">
    
    <div class="col-span-2 mx-auto mt-8 flex flex-wrap">
        <div class="tracking-tight dark:text-gray-400 @if(!$showHistory) font-bold @endif">
            Scheduled Bookings
        </div>
        <div class="mx-2 relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
            <input type="checkbox" wire:model="showHistory" wire:click="changeDisplay" name="toggle" id="toggle" class="border-gray-300 toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" />
            <label for="toggle" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
        </div>
        <div class="tracking-tight dark:text-gray-400 @if($showHistory) font-bold @endif">
            Booking History
        </div>
    </div>
    <style>
        /* CHECKBOX TOGGLE SWITCH */
        /* @apply rules for documentation, these do not work as inline style */
        .toggle-checkbox:checked {
            @apply: right-0 border-green-400;
            right: 0;
            border-color: #68D391;
        }
    
        .toggle-checkbox:checked+.toggle-label {
            @apply: bg-green-400;
            background-color: #68D391;
        }
    </style>
    <div class="my-8 shadow-xl bg-white border dark:bg-gray-800 border-gray-50 dark:border-gray-700 rounded-lg col-span-2 py-20 text-center justify-center items-center" wire:loading wire:target="changeDisplay">
        <i class="fa fa-refresh dark:text-gray-300 fa-2x fa-spin"></i>
    </div>

    @if(!$showHistory)
        <div class="my-8 shadow-xl bg-white dark:bg-gray-800 border border-gray-50 dark:border-gray-700 rounded-lg col-span-2 py-20 text-center justify-center items-center h-full" wire:loading.remove wire:target="changeDisplay">
            <div class="font-semibold tracking-tight text-xl dark:text-gray-300">
                No Scheduled Rides
            </div>
            <a href="{{ route('user.serviceCheckout') }}" class="mt-1 w-1/4 py-2 flex items-center justify-center rounded-md bg-black text-white mx-auto" type="submit">Book A Ride</a>
        </div>
    @elseif($showHistory)
        @forelse($user->userRequest->sortByDesc('id') as $request)
            <div class="grid grid-cols-4 w-full my-5 border border-gray-50 dark:border-gray-700 rounded-lg shadow-lg md:col-span-1 col-span-2 bg-white dark:bg-gray-700" wire:loading.remove wire:target="changeDisplay">
                <div class="lg:col-span-1 col-span-4 flex justify-center items-center" wire:loading.remove wire:target="changeDisplay" style="background-color: #ffffffe8;">
                    <img wire:loading.remove wire:target="changeDisplay" src="{{ $request->service_type->image ? asset('storage/'.$request->service_type->image) : asset('img/classic-utility-jacket.jpg') }}" alt="" class="w-20" />
                </div>
                <form class="p-6 lg:col-span-3 col-span-4" wire:loading.remove wire:target="changeDisplay">
                    <div class="flex flex-wrap items-center dark:text-gray-300">
                        <h1 class="flex-auto text-xl font-semibold tracking-tight">
                            Service: {{ $request->service_type->name ?? '' }}
                        </h1>
                        @if(!empty($request->payment) && !empty($request->payment->total))
                            <div class="font-semibold text-gray-500 dark:text-gray-300 tracking-tight">
                                Amount Paid: {{ currency($request->payment->total) }}
                            </div>
                        @else
                            <div class="font-semibold text-gray-500 dark:text-gray-300 tracking-tight">
                                Status: {{ ucfirst(strtolower($request->status)) }}
                            </div>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 items-baseline mt-4 mb-6 gap-3">
                        <div class="col-span-2 md:col-span-1 text-sm text-gray-500 dark:text-gray-300">
                            <span class="font-bold text-gray-700">Pickup Address: </span><br>
                            {{ Str::limit($request->s_address, 50) ?? '-' }}
                        </div>
                        <div class="col-span-2 md:col-span-1 text-sm text-gray-500 dark:text-gray-300">
                            <span class="font-bold text-gray-700">Destination Address: </span><br>
                            {{ Str::limit($request->d_address, 50) ?? '-' }}
                        </div>
                    </div>
                    <div class="grid grid-cols-2 items-baseline mt-4 mb-6 gap-3">
                        <div class="col-span-2 text-sm text-gray-500 dark:text-gray-300">
                            <span class="font-bold text-gray-700">On: </span>
                            {{ $request->created_at->toDate()->format('d/m/Y') }} at 
                            {{ $request->created_at->toDate()->format('H:i a') }}
                        </div>
                    </div>
                    <div class="flex space-x-3 mb-4 text-sm font-medium">
                        <div class="flex-auto flex space-x-3">
                            <button class="py-2 px-4 flex items-center justify-center rounded-md bg-black text-white" type="button" @click="showRequestDetails=!showRequestDetails" wire:click="$emit('updateCurrentRequest', {{ $request->id }})">View Order Details</button>
                        </div>
                    </div>
                </form>
            </div>
        @empty
            <div class="my-8 shadow-xl bg-white border border-gray-50 dark:border-gray-700 rounded-lg col-span-2 py-20 text-center justify-center items-center" wire:loading.remove wire:target="changeDisplay">
                <div class="font-semibold tracking-tight text-xl">
                    No services requested Yet.
                </div>
                <a href="{{ route('user.serviceCheckout') }}" class="mt-1 w-1/4 py-2 flex items-center justify-center rounded-md bg-black text-white mx-auto" type="submit">Book A Ride</a>
            </div>
        @endforelse
    @endif

    
    {{-- Request Details Modal --}}
    @if($userRequest)
        <div x-show="showRequestDetails">
            <div
                x-transition:leave="transition ease-in duration-1000" 
                x-transition:leave-start="opacity-100" 
                x-transition:leave-end="opacity-0" 
                class="fixed top-0 right-0 w-full p-2 space-y-2 text-gray-600 h-full flex justify-center items-center bg-white shadow-md dark:border-gray-700 dark:text-gray-300 dark:bg-gray-700" aria-label="submenu" style="z-index: 30; background-color: rgba(255, 255, 255, .15); overflow-y: hidden; backdrop-filter: blur(5px);">
                <div class="rounded-full cursor-pointer border shadow-xl bg-white flex justify-center items-center absolute" style="top: 40px; right: 40px;" @click="showRequestDetails=!showRequestDetails">
                    <i class="fa fa-times p-3 px-4" style="font-size: 20px;"></i>
                </div>
                <div style="max-height: 80%;" class="w-full overflow-y-scroll">
                    <div class="bg-white dark:bg-gray-800 px-4 rounded shadow-xl border border-gray-700 py-10 flex flex-col justify-center items-center w-full md:w-4/5 mx-auto h-4/5">    
                        <div class="flex w-full px-2 justify-between items-center text-sm h-full">
                            <span>
                                <span class="font-semibold">Booking Id: </span>
                                <span class="font-mono">{{ !empty(config('constants.booking_id_prefix')) ? config('constants.booking_id_prefix').'#' : '' }}{{ $userRequest->booking_id }}</span>
                            </span>
                            <span>
                                <span class="font-semibold">Date: </span>
                                <span class="font-mono">{{ $userRequest->created_at->toDate()->format('d/m/Y') }}</span>
                            </span>
                        </div>
                        <hr class="dark:border-gray-700 w-full mt-1 mb-2">
                        <div class="grid grid-cols-5 md:gap-6 gap-0 px-2 w-full">
                            <div class="md:col-span-2 mb-2 text-sm col-span-5">
                                <span class="text-gray-700 dark:text-gray-300 font-semibold">From: </span><br>
                                <span>
                                    {{ $userRequest->s_address ?? '' }}
                                </span>
                            </div>
                            <div class="md:col-span-1 md:block hidden"></div>
                            <div class="md:col-span-2 mb-2 text-sm col-span-5">
                                <span class="text-gray-700 dark:text-gray-300 font-semibold">To: </span><br>
                                <span>
                                    {{ $userRequest->d_address ?? ''}}
                                </span>
                            </div>
                        </div>
                        <hr class="dark:border-gray-700 w-full mt-1 mb-2">
                        <div class="grid grid-cols-2 md:gap-2 gap-0 px-2 w-full">
                            <div class="mt-2 overflow-x-scroll col-span-2 md:col-span-1" style="width: 98%;">
                                <table class="w-full rounded whitespace-no-wrap">
                                    <thead>
                                        <tr class="border-b dark:border-gray-700">
                                            <td colspan="2" class="text-gray-700 dark:text-gray-400 px-2 py-3 font-semibold">
                                                Request Details
                                            </td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                            <td class="px-4 py-3">{{ __('crud.admin.serviceTypes.index') }}</td>
                                            <td>{{ $userRequest->service_type->name }}</td>
                                        </tr>
                                        <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                            <td class="px-4 py-3">{{ __('crud.admin.serviceTypes.name') }} {{ __('crud.inputs.description') }}</td>
                                            <td>{{ $userRequest->service_type->description ? $userRequest->service_type->description : '-' }}</td>
                                        </tr>
                                        <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                            <td class="px-4 py-3">{{ __('crud.navlinks.request') }} {{ __('crud.inputs.status') }}</td>
                                            <td>{{ $userRequest->status ? ucfirst(strtolower($userRequest->status)) : '-' }}</td>
                                        </tr>
                                        @if($userRequest->status == 'CANCELLED')
                                            <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                <td class="px-4 py-3">{{ __('crud.general.cancelled_by') }}</td>
                                                <td>{{ $userRequest->cancelled_by ? ucfirst(strtolower($userRequest->cancelled_by)) : '-' }}</td>
                                            </tr>
                                            @if($userRequest->cancelled_by == 'USER')
                                                <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                    <td class="px-4 py-3">{{ __('crud.admin.cancelReasons.name') }}</td>
                                                    <td>{{ $userRequest->cancel_reason ? ucfirst(strtolower($userRequest->cancel_reason)) : '-' }}</td>
                                                </tr>
                                            @endif
                                        @endif
                                        @if($userRequest->payment)
                                            <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                <td class="px-4 py-3">{{ __('crud.admin.providers.name') }} {{ __('crud.inputs.name') }}</td>
                                                <td>{{ $userRequest->provider ? $userRequest->provider->name : '-' }}</td>
                                            </tr>
                                            <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                <td class="px-4 py-3">{{ __('crud.admin.providers.name') }} {{ __('crud.inputs.contact_number') }}</td>
                                                <td>{{ $userRequest->provider ? $userRequest->provider->mobile : '-' }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            @if($userRequest->payment)
                                <div class="mt-2 overflow-x-scroll col-span-2 md:col-span-1" style="width: 98%;">
                                    <table class="w-full rounded whitespace-no-wrap">
                                        <thead>
                                            <tr class="border-b dark:border-gray-700">
                                                <td colspan="2" class="text-gray-700 dark:text-gray-400 px-2 py-3 font-semibold">
                                                    Payment Details
                                                </td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                <td class="px-4 py-3">{{ __('crud.inputs.payment_mode') }}</td>
                                                <td>
                                                    {{ $userRequest->payment ? $userRequest->payment->payment_mode : '-' }}
                                                </td>
                                            </tr>
                                            <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                <td class="px-4 py-3">{{__('crud.payment.base_price') ?? ''}}</td>
                                                <td>{{ currency($userRequest->payment->fixed ) ?? ''}}</td>
                                            </tr>
                                            
                                            @if($userRequest->service_type->calculator == 'MIN')
                                                <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                    <td class="px-4 py-3">{{__('crud.payment.minutes_price') ?? ''}}</td>
                                                    <td>{{ currency($userRequest->payment->minute) ?? '' }}</td>
                                                </tr>
                                            @endif
                                            @if($userRequest->service_type->calculator == 'HOUR')
                                                <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                    <td class="px-4 py-3">{{__('crud.payment.hours_price') ?? ''}}</td>
                                                    <td>{{ currency($userRequest->payment->hour) ?? '' }}</td>
                                                </tr>
                                            @endif
                                            @if($userRequest->service_type->calculator == 'DISTANCE')
                                                <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                    <td class="px-4 py-3">{{__('crud.payment.distance_price') ?? ''}}</td>
                                                    <td>{{ currency($userRequest->payment->distance) ?? '' }}</td>
                                                </tr>
                                            @endif
                                            @if($userRequest->service_type->calculator == 'DISTANCEMIN')
                                                <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                    <td class="px-4 py-3">{{__('crud.payment.minutes_price') ?? ''}}</td>
                                                    <td>{{ currency($userRequest->payment->minute) ?? '' }}</td>
                                                </tr>
                                                <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                    <td class="px-4 py-3">{{__('crud.payment.distance_price') ?? ''}}</td>
                                                    <td>{{ currency($userRequest->payment->distance) ?? '' }}</td>
                                                </tr>
                                            @endif
                                            @if($userRequest->service_type->calculator == 'DISTANCEHOUR')
                                                <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                    <td class="px-4 py-3">{{__('crud.payment.hours_price') ?? ''}}</td>
                                                    <td>{{ currency($userRequest->payment->hour) ?? '' }}</td>
                                                </tr>
                                                <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                    <td class="px-4 py-3">{{__('crud.payment.distance_price') ?? ''}}</td>
                                                    <td>{{ currency($userRequest->payment->distance) ?? '' }}</td>
                                                </tr>
                                            @endif
    
                                            {{-- Discount --}}
                                            @if ($userRequest->payment->discount != 0)
                                                <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                    <td class="px-4 py-2">{{__('crud.payment.discount') ?? ''}}</td>
                                                    <td><span class="mono"> - {{ currency($userRequest->payment->discount) ?? '' }}</span></td>
                                                </tr>
                                            @endif
    
                                            {{-- Tax Details --}}
                                            <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                <td class="px-4 py-2">{{__('crud.payment.tax') ?? ''}} ({{ Config::get('constants.tax_percentage') }}%)</td>
                                                <td><span class="mono">{{ currency($userRequest->payment->tax) ?? '' }}</span></td>
                                            </tr>
    
                                            {{-- Tips --}}
                                            @if ($userRequest->payment->tips != 0)
                                                <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                    <td class="px-4 py-2">{{__('crud.payment.tip') ?? ''}}</td>
                                                    <td>{{ currency($userRequest->payment->tips) ?? '' }}</td>
                                                </tr>
                                            @endif
    
                                            {{-- Paid --}}
                                            <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                <td class="px-4 py-2">{{__('crud.payment.paid') ?? ''}}</td>
                                                <td>{{ currency($userRequest->payment->payable + $userRequest->payment->tips) ?? '' }}</td>
                                            </tr>
    
                                            {{-- Round Off Numbers --}}
                                            <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                <td class="px-4 py-2">{{__('crud.payment.round_off') ?? ''}}</td>
                                                <td>{{ currency($userRequest->payment->round_of) ?? '' }}</td>
                                            </tr>
    
                                            {{-- Total Amount Payable --}}
                                            <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                                                <td class="px-4 py-2">{{__('crud.payment.total') ?? ''}}</td>
                                                <td><strong class="text-muted font-mono">{{ currency($userRequest->payment->total+$userRequest->payment->tips) ?? '' }}</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>