<div class="md:col-span-2 col-span-5 w-full relative" style="box-shadow: -2px 0px 10px -5px #aaaaaa; height: 500px;">
    <script>
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        function alpineVars() {
            return {
                showFair: @entangle('showFair'), 
                showChat: false,
                toggleChatMenu: async function() {
                    this.showChat = !this.showChat;
                    
                    msgContainer = document.getElementById('messageContainer');
                    msgBody = document.getElementById('messageContainerBody');
                    var count = 0;

                    while(msgContainer.offsetHeight == 0 && count < 100) {
                        count++;
                        await sleep(50);
                        msgBody.scrollTop = msgContainer.offsetHeight;
                    }
                }
            }
        }
    </script>
    <div class="overflow-y-scroll md:col-span-2 col-span-5 py-3 overflow-x-scroll w-full h-full" x-data="alpineVars()">
        <style>
            #messageContainerBody {
                scroll-behavior: smooth;
            }
        </style>
    
        @if(empty($request_id) && $havePendingRequest)
            <div class="flex justify-center items-center h-full flex-col">
                <i class="fa fa-refresh text-blue-800 dark:text-blue-100 fa-spin fa-2x mb-1"></i>
                <div class="dark:text-blue-100">
                    Loading OnGoing Request
                </div>
            </div>
        @elseif($request_id && $status == 'COMPLETED')
            {{-- Let's Rate Your Request # {{ $request_id }} --}}
            <h2 class="font-bold text-xl text-center mb-2 dark:text-gray-300">Rate Your Experience</h2>
            <div>
                <img class="mx-auto w-20 h-20" src="{{ $userRequest->provider->avatar ? asset('storage/'.$userRequest->provider->avatar) : asset('img/avatar.png') }}" alt="">
            </div>
            <div class="text-center">
                <span class="text-sm dark:text-gray-400">{{ $userRequest->provider->name }}</span>
            </div>
            <div class="flex items-center justify-center text-sm dark:text-gray-400">
                Request Id: {{ $userRequest->id }}
            </div>
            <div class="w-full relative">
                <x-form action="#" class="px-10 pb-6" wire:submit.prevent="saveRating" method="put" id="ratingForm">
                    <fieldset class="starability-basic pb-2 mx-auto w-full flex justify-center">
                        <input type="radio" id="no-rate" class="input-no-rate" value="0" checked aria-label="No rating." name="rating" />
                
                        <input type="radio" id="rate1" value="1" wire:model.defer="rating" name="rating" />
                        <label for="rate1">1 star.</label>
                
                        <input type="radio" id="rate2" value="2" wire:model.defer="rating" name="rating" />
                        <label for="rate2">2 stars.</label>
                
                        <input type="radio" id="rate3" value="3" wire:model.defer="rating" name="rating" />
                        <label for="rate3">3 stars.</label>
                
                        <input type="radio" id="rate4" value="4" wire:model.defer="rating" name="rating" />
                        <label for="rate4">4 stars.</label>
                
                        <input type="radio" id="rate5" value="5" wire:model.defer="rating" name="rating" />
                        <label for="rate5">5 stars.</label>
                
                        <span class="starability-focus-ring"></span>
                    </fieldset>
        
                    <textarea rows="10" label="" placeholder="Leave A Comment!" id="" wire:model.defer="comment" class="appearance-none w-full p-4 text-sm font-semibold leading-none border rounded outline-none dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500" required></textarea>
        
                    <div class="flex justify-end">
                        <button type="submit" class="w-full py-2 px-5 mt-4 rounded text-sm bg-blue-500 hover:bg-blue-600 text-white" wire:loading.remove wire:target="saveRating" type="submit">Submit</button>
                        <button type="submit" class="w-full py-2 px-5 mt-4 rounded text-sm bg-blue-500 hover:bg-blue-600 text-blue-100 flex justify-center items-center" wire:loading wire:target="saveRating" type="submit">
                            <i class="fa fa-refresh fa-spin"></i> Submitting Response
                        </button>
                    </div>
                </x-form>
            </div>
        @elseif($request_id && in_array($status, ['STARTED', 'ARRIVED', 'PICKEDUP']))
            <div class="relative w-full h-full">
                <div class="flex justify-center items-center flex-col h-full w-full">
                    @if($status != 'PICKEDUP')
                        <a class="bg-indigo-500 rounded-full h-10 w-10 flex justify-center cursor-pointer items-center top-2 text-lg right-16 absolute" href="tel:{{ $userRequest->provider->mobile }}">
                            <i class="fa text-white fa-phone"></i>
                        </a>
                        <div class="bg-red-500 rounded-full h-10 w-10 flex justify-center cursor-pointer items-center top-2 text-lg right-5 absolute" x-on:click="toggleChatMenu()">
                            <i class="fa text-white fa-comment"></i>
                        </div>
                    @endif
                    <div class="flex justify-center items-center">
                        <img class="border border-gray-100 rounded-full h-20 w-20" src="{{ $userRequest->provider->avatar ? asset('storage/'.$userRequest->provider->avatar) : asset('img/avatar.png') }}" alt="">
                    </div>
                    <div class="font-semibold dark:text-gray-300">
                        {{ $userRequest->provider->name }}
                    </div>
                    <div class="text-sm dark:text-gray-300">
                        will be at your service.
                    </div>
                    <div class="text-sm mt-1 text-center dark:text-gray-300">
                        @if($status == 'STARTED') 
                            <span class="font-semibold">Status - </span>Will Be At Your Service 
                        @elseif($status == 'ARRIVED') 
                            <span class="font-semibold">Status - </span>Is At Your Residence 
                        @elseif($status == 'PICKEDUP')
                            <span class="font-semibold">Status - </span>Has Started Service
                            <br>
                            {{-- <span class="font-semibold dark:text-gray-300">
                                "The road ahead may be long and winding but you'll make it there safe and sound."
                            </span> --}}
                        @endif
                    </div>
                    @if($status != 'PICKEDUP')
                        <div class="mt-2 flex justify-end">
                            <button type="submit" class="py-2 px-5 mt-4 rounded text-sm bg-red-500 hover:bg-red-600 text-white flex justify-center items-center" wire:click="getCancellationReasons()" wire:target="getCancellationReasons" type="button" wire:loading.remove>
                                Cancel Ride
                            </button>
                            <button type="submit" class="py-2 px-5 mt-4 rounded text-sm bg-red-500 hover:bg-red-600 text-white flex justify-center items-center" wire:target="getCancellationReasons" type="button" wire:loading>
                                <i class="fa fa-refresh fa-spin"></i>&nbsp;Getting Cancelling Options
                            </button>
                        </div>
                    @else 
                    @endif
                    <div x-show.transition.origin.bottom="showChat" class="absolute top-0 h-full w-full bg-white">
                        <div class="flex items-center justify-between border-b border-gray-200 px-2 pt-1" style="height: 10%;">
                            <span class="flex items-center">
                                <img class="border border-gray-100 rounded-full h-12 w-12" src="{{ $userRequest->provider->avatar ? asset('storage/'.$userRequest->provider->avatar) : asset('img/avatar.png') }}" alt="">&nbsp;{{ $userRequest->provider->name }}
                            </span>
                            <span class="bg-red-500 rounded-full h-8 w-8 flex justify-center cursor-pointer items-center" x-on:click="toggleChatMenu()">
                                <i class="fa text-white fa-times"></i>
                            </span>
                        </div>
                        <div class="relative h-full w-full justify-center" style="height: 90%;">
                            <div id="messageContainerBody" class="border-l border-r h-full overflow-y-scroll" style="height: 93%">
                                {{-- 
                                    TODO: make cursor pointer and show time and date of message.
                                --}}
                                <style>
                                    .sent:before { position: absolute; right: -14px; content:""; border-top:7px solid white; border-bottom:7px solid white; border-left:7px solid #f2f5fa; border-right:7px solid white;}
                                    .received:before { position: absolute; left: -14px; content:""; border-top:7px solid white; border-bottom:7px solid white; border-left:7px solid white; border-right:7px solid #e6eaf1;}
                                </style>
                                {{--
                                    TODO: Check why this is not working.
                                --}}
                                <div class="relative p-5" id="messageContainer">
                                    @if($chat && $chat->count())
                                        @forelse ($chat as $msg)
                                            @if($msg->type == 'pu')
                                                <div class="flex @if(!$loop->first) mt-5 @endif">
                                                    <div class="flex justify-center items-top" style="max-width: 80%; width: fit-content;">
                                                        <img class="border border-gray-100 rounded-full h-12 w-12" src="{{ $userRequest->provider->avatar ? asset('storage/'.$userRequest->provider->avatar) : asset('img/avatar.png') }}" alt="">
    
                                                        <span class="relative received bg-gray-100 rounded ml-5 p-3 cursor-pointer" title="{{ $msg->created_at->diffForHumans() }}">
                                                            {{ $msg->message }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @elseif($msg->type == 'up')
                                                <div class="flex justify-end @if(!$loop->first) mt-5 @endif">
                                                    <div class="flex justify-center items-top" style="max-width: 80%; width: fit-content;">
                                                        <span class="relative sent bg-gray-50 rounded mr-5 p-3 cursor-pointer" title="{{ $msg->created_at->diffForHumans() }}">
                                                            {{ $msg->message }}
                                                        </span>
                                                        
                                                        <img class="border border-gray-100 rounded-full h-12 w-12" src="{{ $userRequest->user->avatar ? asset('storage/'.$userRequest->user->avatar) : asset('img/avatar.png') }}" alt="">
                                                    </div>
                                                </div>
                                            @endif
                                        @empty
                                            No Chat Yet.
                                        @endforelse
                                    @else
                                        No Chat Yet.
                                    @endif
                                </div>
                            </div>
                            <input type="text" class="absolute bottom-0 px-2 focus:outline-none left-0 w-full border border-r-0" style="height: 7%; z-index: 10; width: 94%;" wire:model.defer="message" wire:keydown.enter="sendMessage()" autofocus>
                            <i class="fa fa-paper-plane bg-red-500 text-white absolute flex justify-center items-center right-0" style="z-index: 11; height: 7%; width:6%;"></i>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($request_id && $status == 'DROPPED')
            <h2 class="text-center transition-opacity duration-1500 delay-500 text-xl sm:text-3xl font-semibold text-gray-800 dark:text-gray-100 px-4 py-6 sm:px-6 pb-1 tracking-tight">Ride Details</h2>
            
            <div x-show.transition.origin.top.left="showFair" class="w-full flex justify-end px-4 flex-col">
                <table class="w-full rounded whitespace-no-wrap">
                    <thead>
                        <tr class="border-b">
                            <td colspan="2" class="text-gray-700 dark:text-gray-300 px-4 py-3 font-semibold">Payment Details</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-400 border-b">
                            <td class="px-4">{{ __('crud.inputs.booking_id') }}</td>
                            <td class="py-3" wire:loading.remove wire:target="updateRates">{{ $userRequest->booking_id ?? '-' }}</td>
                            <td class="flex items-center justify-center w-full px-4 py-3" wire:loading wire:target="updateRates"><i class="fa fa-refresh fa-spin"></i></td>
                        </tr>
                        <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-400 border-b">
                            <td class="px-4 w-1/3">{{ __('crud.inputs.service_location') }}</td>
                            <td class="py-3" wire:loading.remove wire:target="updateRates">{{ $userRequest->s_address ?? '-' }}</td>
                            <td class="flex items-center justify-center w-full px-4 py-3" wire:loading wire:target="updateRates"><i class="fa fa-refresh fa-spin"></i></td>
                        </tr>
                        {{-- <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-400 border-b">
                            <td class="px-4">{{ __('crud.inputs.distance') }}</td>
                            <td class="py-3" wire:loading.remove wire:target="updateRates">{{ $userRequest->distance ?? '-' }} km</td>
                            <td class="flex items-center justify-center w-full px-4 py-3" wire:loading wire:target="updateRates"><i class="fa fa-refresh fa-spin"></i></td>
                        </tr>
                        <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-400 border-b">
                            <td class="px-4">{{ __('crud.payment.base_price') }}</td>
                            <td class="py-3" wire:loading.remove wire:target="updateRates">{{ currency($userRequest->payment->fixed) ?? '-' }}</td>
                            <td class="flex items-center justify-center w-full px-4 py-3" wire:loading wire:target="updateRates"><i class="fa fa-refresh fa-spin"></i></td>
                        </tr>
                        <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-400 border-b">
                        <td class="px-4">{{__('crud.payment.tax') ?? ''}} ({{ Config::get('constants.tax_percentage') }}%)</td>
                            <td class="py-3" wire:loading.remove wire:target="updateRates">{{ currency($userRequest->payment->tax) ?? '-' }}</td>
                            <td class="flex items-center justify-center w-full px-4 py-3" wire:loading wire:target="updateRates"><i class="fa fa-refresh fa-spin"></i></td>
                        </tr>
                        <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-400 border-b">
                            <td class="px-4">{{ __('crud.admin.userRequests.estimated_fare') }}</td>
                            <td class="py-3" wire:loading.remove wire:target="updateRates">{{ currency($userRequest->payment->fixed + $userRequest->payment->tax) ?? '-' }}</td>
                            <td class="flex items-center justify-center w-full px-4 py-3" wire:loading wire:target="updateRates"><i class="fa fa-refresh fa-spin"></i></td>
                        </tr>
                        <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-400 border-b">
                            <td class="px-4">{{ __('crud.admin.promocodes.coupon') }}</td>
                            <td class="py-3" wire:loading.remove wire:target="updateRates">{{ $coupon ?? '-' }}</td>
                            <td class="flex items-center justify-center w-full px-4 py-3" wire:loading wire:target="updateRates"><i class="fa fa-refresh fa-spin"></i></td>
                        </tr>
                        <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-400 border-b">
                            <td class="px-4">{{ __('crud.inputs.total_amount') }}</td>
                            <td class="py-3" wire:loading.remove wire:target="updateRates">{{ currency($userRequest->payment->total) ?? '-' }}</td>
                            <td class="flex items-center justify-center w-full px-4 py-3" wire:loading wire:target="updateRates"><i class="fa fa-refresh fa-spin"></i></td>
                        </tr> --}}
                    </tbody>
                </table>
            </div>
        @elseif($request_id && $status == 'CANCEL')
            {{-- Cancellation Reasons Should Be Dipslayed here --}}
            <div class="px-6 my-4 h-10 relative flex items-center justify-between">
                <h2 class="text-center transition-opacity duration-1500 delay-500 text-xl font-semibold text-gray-800 dark:text-gray-100 tracking-tight">Select Reason For Cancelling Request</h2>
                <button class="absolute focus:outline-none right-3 top-0 bg-red-400 w-10 h-10 rounded-full flex justify-center items-center cursor:pointer" wire:click="exitRequestCancel" wire:target="exitRequestCancel">
                    <i wire:target="exitRequestCancel" wire:loading.remove class="fa fa-times text-white"></i>
                    <i wire:target="exitRequestCancel" wire:loading class="fa fa-refresh fa-spin text-white"></i>
                </button>
            </div>
            <div class="px-10 relative">
                <x-inputs.select wire:ignore name="language" space="md:w-full" wire:model.defer="selectedReason">
                    @forelse ($cancellationReasons as $cr)
                        <option value="{{ $cr->id }}">{{ $cr->reason }}</option>
                    @empty
                    @endforelse
                </x-inputs.select>
                
                <button class="text-base font-medium rounded-lg px-4 bg-red-500 text-white py-2 float-right text-center cursor-pointer" wire:loading.remove wire:target="cancelRequest" wire:click="cancelRequest()">
                    {{-- Timer --}}
                    Cancel Request
                </button>
                <button class="text-base font-medium rounded-lg px-4 bg-red-500 text-white py-2 float-right text-center cursor-pointer" wire:loading wire:target="cancelRequest">
                    <x-wait></x-wait> Cancelling Request
                </button>
            </div>
        @else
            {{-- User Pickup Location --}}
            <div class="w-full px-4 mb-4 md:mb-0 md:full">
                <div class="mb-6">
                    <input wire:ignore wire:modal.defer="s_address" id="pac-input" autocomplete="off" class="controls appearance-none w-full p-4 text-xs font-semibold leading-none rounded outline-none bg-gray-50 dark:bg-gray-700 dark:text-gray-300" type="text" placeholder="Search {{ __('crud.dispatcher.pickup_location') }}"/>
                </div>
            </div>
    
            {{-- Total Distance between source & destination --}}
            <input hidden type="text" wire:modal.defer="distance" id="total">

            <div>
                @if($availableServices)
                    <x-inputs.select wire:ignore name="serviceSelector" space="md:w-full" wire:model.defer="serviceType" onChange="getChildServices()">
                        
                    </x-inputs.select>
                @endif
            </div>
    
            <div class="w-full flex justify-end px-4 flex-col" wire:ignore>
                <table class="w-full rounded whitespace-no-wrap">
                    <thead>
                        <tr class="border-b dark:border-gray-700">
                            <td colspan="2" class="text-gray-700 dark:text-gray-300 px-4 py-3 font-semibold">Payment Details</td>
                        </tr>
                    </thead>
                        <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                            <td class="px-4">{{ __('crud.payment.base_price') }}</td>
                            <td class="py-3" id="basePrice">-</td>
                        </tr>
                        <tr class="text-gray-700 dark:bg-gray-800 text-sm dark:text-gray-300 border-b dark:border-gray-700">
                            <td class="px-4">{{ __('crud.payment.daily_rate') }}</td>
                            <td class="py-3" id="dailyRate">-</td>
                        </tr>
                    </tbody>
                </table>
                @if(empty($request_id) && empty($status))
                    {{-- There is No Request. Button To Send Request. --}}
                    <div class="mt-2 flex justify-end">
                        <button class="right-0 inline-block py-1 px-4 leading-loose bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition duration-200 text-sm" type="button" wire:click="sendRequest" wire:target="sendRequest" wire:loading.remove>Send Request</button>
                        <button class="right-0 inline-block py-1 px-4 leading-loose bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition duration-200 text-sm" type="button" wire:target="sendRequest" wire:loading><i class="fa fa-refresh fa-spin"></i> Sending Request</button>
                    </div> 
                @elseif($request_id)
                    @if($status == 'SEARCHING')
                        {{-- If There Is Request & status is 'SEARCHING' --}}
                        <div class="mt-2 flex justify-end">
                            <button type="submit" class="w-full py-2 px-5 mt-4 rounded text-sm bg-blue-500 hover:bg-blue-600 text-blue-100 flex justify-center items-center" wire:target="saveRating" type="submit">
                                <x-wait></x-wait> Searching Ride
                            </button>
                        </div>
                        <button type="submit" class="py-2 px-5 mt-2 rounded text-sm bg-red-500 hover:bg-red-600 text-white flex justify-center items-center" wire:click="getCancellationReasons()" wire:target="getCancellationReasons" type="button" wire:loading.remove>
                            Cancel Ride
                        </button>
                        <button type="submit" class="py-2 px-5 mt-4 rounded text-sm bg-red-500 hover:bg-red-600 text-white flex justify-center items-center" wire:target="getCancellationReasons" type="button" wire:loading>
                            <i class="fa fa-refresh fa-spin"></i>&nbsp;Getting Cancelling Options
                        </button>
                    @endif
                @endif
            </div>
        @endif
    </div>
    @push('endScripts')

        <script>
            let serviceSelector = document.getElementById('serviceSelector');
            const availableServices = {!! $availableServices !!};
            let currentService = null;
            let basePrice = document.getElementById('basePrice');
            let dailyRate = document.getElementById('dailyRate');
            console.log(availableServices);
            let serviceSequence = [];

            var i = 0;
            {{--
                This will fill the select Menu for services.
                @arguments
                    providedServices :- An array of services from which the services are to be entered.
                    parentService :- Name of the Parent Service but only in the case the current provided services are the child services of some service.
                    isService :- Boolean value. If false means it is not a service but a sub-service.

                @returns void
            --}}
            function fillServices(providedServices, parentService = null)
            {
                for(i = 0; i < providedServices.length; i++) {
                    let newOption = new Option(providedServices[i].name.{{ auth('web')->user()->language ?: config('app.fallback_locale') }}, providedServices[i].id);
                    var a = serviceSelector.add(newOption, undefined);
                    newOption.setAttribute('data-index', i);
                }

                if(!parentService) {
                    var newOption = new Option("Select From Various Services", null, true);
                    var a = serviceSelector.add(newOption, undefined);
                    newOption.setAttribute('hidden', 'hidden');
                    serviceSelector.value = null;
                }
                else {
                    var newOption = new Option("Select Sub-Services For Category - " + parentService, null, true);
                    var a = serviceSelector.add(newOption, undefined);
                    newOption.setAttribute('hidden', 'hidden');
                    
                    var newOption = new Option("Go Back", "goBack", true);
                    var a = serviceSelector.add(newOption, undefined);
                }
            }

            function emptyList()
            {
                var length = serviceSelector.options.length;
                for(i = length-1; i >= 0; i--) {
                    serviceSelector.options[i] = null;
                }
            }

            function getChildServices()
            {
                {{--
                    Push the current selected index to the end of the serviceSequence which is array of services & services which we are going to.
                --}}
                
                if(serviceSelector.value == 'goBack') {
                    getParentServices();
                    return true;
                }

                var index = serviceSelector.options[serviceSelector.selectedIndex].getAttribute('data-index');
                serviceSequence.push(index);

                getServices();
            }

            function getServices()
            {
                let i = 1;
                let collectServices = null;
                {{--
                    Iterate through the services to get to the last selected service.
                    TODO: Save the last value so we do not have to iterate through the entire array each time.
                --}}
                serviceSequence.forEach(function(currentValue, index) {
                    if(!collectServices) {
                        collectServices = availableServices[currentValue];
                    }
                    else {
                        collectServices = collectServices.children_recursive[currentValue];
                    }

                    if(serviceSequence.length == i && collectServices.children_recursive.length == 0) {
                        // If serviceSequence is finished & Recursive Childrens doesn't Exist for current service.
                        currentService = collectServices;
                        console.log(currentService);
                        basePrice.innerHTML = currentService.fixed;
                        dailyRate.innerHTML = currentService.price;
                    }
                    else {
                        i++;
                        basePrice.innerHTML = '-';
                        dailyRate.innerHTML = '-';
                    }
                });

                {{--
                    Take the last service and fill the select menu with it's child elements. If there are no child elements then it means that it itself is a child so we remove the last element from the array.
                --}}
                if(serviceSequence.length == 0) {
                    emptyList();
                    fillServices(availableServices);
                }
                else {
                    if(collectServices.children_recursive.length) {
                        {{--
                            Child Service Exists
                        --}}
                        var parentServiceName = serviceSelector.options[serviceSelector.selectedIndex].text;

                        emptyList();

                        fillServices(collectServices.children_recursive, parentServiceName);
                        serviceSelector.value = null;
                    }
                    else {
                        {{--
                            There is no child.
                        --}}
                        if(serviceSequence.length > 0) {
                            serviceSequence.pop();
                        }
                    }
                }
            }

            {{--
                Gets the parents & it's siblings of the current node.
            --}}
            function getParentServices()
            {
                serviceSequence.pop();
                getServices();
            }

            fillServices(availableServices);
        </script>
        
    @endpush
</div>