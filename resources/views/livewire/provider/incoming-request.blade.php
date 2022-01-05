<div>
    <script>
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        function chatFunction()
        {
            return {
                incomingRequest:@entangle('incomingRequest'),
                noIncomingRequest:false,
                showChat: false,
                toggleChatMenu: async function() {
                    this.showChat = !this.showChat;
                    
                    msgContainer = document.getElementById('messageContainer');
                    msgBody = document.getElementById('messageContainerBody');
                    var count = 0;
                    var mainArticle = document.getElementById('mainArticle');

                    if(mainArticle.classList.contains('overflow-y-scroll')){
                        mainArticle.scrollTop = 0;
                        mainArticle.classList.remove('overflow-y-scroll');
                        mainArticle.classList.add('overflow-y-hidden');
                    }
                    else {
                        mainArticle.classList.add('overflow-y-scroll');
                        mainArticle.classList.remove('overflow-y-hidden');
                    }

                    while(msgContainer.offsetHeight == 0 && count < 100) {
                        count++;
                        await sleep(50);
                        msgBody.scrollTop = msgContainer.offsetHeight;
                    }
                }
            }
        }
    </script>

    <style>
        #messageContainerBody {
            scroll-behavior: smooth;
        }
    </style>

    <div x-data="chatFunction()">
        {{-- Request Modal --}}
        @if($thereIsRequest  === true)
            {{-- Modal Close Button --}}
            <button class="relative align-middle rounded-md focus:outline-none focus:shadow-outline-purple" x-on:click="incomingRequest=!incomingRequest" title="Incoming Request" wire:key="thereIsRequestButton">
                <i class="fa fa-car text-gray-400 dark:text-gray-300 bell"></i>
                {{-- Dot For Availability of Notification --}}
                <span aria-hidden="true" class="absolute top-0 right-0 inline-block w-3 h-3 transform translate-x-1 -translate-y-1 bg-red-600 border-2 border-white rounded-full dark:border-gray-800"></span>
            </button>

            {{-- loading Modal --}}
            {{-- <div x-show="incomingRequest" wire:key="thereIsRequestModal" wire:loading>
                <div
                    class="fixed top-0 right-0 w-full p-2 space-y-2 text-gray-600 h-full flex justify-center items-center bg-white shadow-md dark:border-gray-700 dark:text-gray-300 dark:bg-gray-700" style="z-index: 30; background-color: rgba(255, 255, 255, .15); overflow-y: hidden; backdrop-filter: blur(5px);"
                >
                    <div class="rounded-full cursor-pointer border shadow-xl bg-white flex justify-center items-center absolute" style="top: 40px; right: 40px;" x-on:click="incomingRequest=false">
                        <i class="fa fa-times p-3 px-4" style="font-size: 20px;"></i>
                    </div>
                    <div class="lg:w-3/5 w-full h-3/5 bg-white rounded items-center flex dark:bg-gray-900 shadow-xl border-gray-400 dark:border-gray-600">
                        <div class="w-full relative text-center">
                            <i class="fa fa-refresh text-blue-900 dark:text-blue-200 fa-2x fa-spin"></i>
                        </div>
                    </div>
                </div>
            </div> --}}
            <div x-show="incomingRequest" wire:key="thereIsRequestModal">
                {{-- Background Blur Cover --}}
                <div class="fixed top-0 right-0 w-full p-2 space-y-2 text-gray-600 h-full flex justify-center items-center bg-white shadow-md dark:border-gray-700 dark:text-gray-300 dark:bg-gray-700 rounded" style="z-index: 30; background-color: rgba(114, 24, 24, 0.15); overflow-y: hidden; backdrop-filter: blur(5px);">

                    {{-- Modal Close Button --}}
                    <div class="rounded-full cursor-pointer border shadow-xl bg-white flex justify-center items-center absolute" style="top: 23px; right: 23px; z-index: 100;" x-on:click="incomingRequest=false">
                        <i class="fa fa-times p-3 px-4" style="font-size: 20px;"></i>
                    </div>

                    <div class="h-full flex justify-center items-center w-full lg:p-16 p-4 overflow-hidden">
                        <article class="relative dark:bg-gray-800 shadow-xl text-gray-600 bg-white mx-auto border dark:border-gray-800 rounded overflow-y-scroll grid grid-cols-4" style="max-height: 80%; height: fit-content; min-width: 50%;" id="mainArticle" wire:ignore.self>
                            @if(in_array($status, ['STARTED', 'ARRIVED', 'PICKEDUP', 'CANCEL']))
                                <div class="p-4 col-span-4 lg:col-span-2 flex justify-center items-center @if($status == 'CANCEL') hidden @endif"> 
                                    <div class="flex flex-col justify-center items-center h-full w-full" style="min-height: 400px;" id="map" wire:ignore>
                                        <div>
                                            <i class="fa fa-refresh fa-spin fa-2x dark:text-gray-300"></i>
                                        </div>
                                        <div class="dark:text-gray-300">
                                            Loading Map
                                        </div>
                                    </div>
                                </div>
                            @endif
                            {{-- Original Modals --}}
                            {{-- 
                                ! Divider Classes:- 
                                divide-y divide-gray-200 dark:divide-gray-500 border-b border-gray-200 dark:border-gray-500 
                            --}}
                            @if(strcmp($status, 'SEARCHING') == 0) 
                                {{-- Request Accept Reject --}}
                                <div class="p-4 col-span-4 flex justify-center items-center">
                                    <div class="w-full relative">
                                        <article class="dark:text-gray-300">
                                            <h2 class="transition-opacity duration-1500 delay-500 text-xl sm:text-2xl font-semibold text-black dark:text-gray-100 px-4 py-6 sm:px-6 pb-1">Ride Details</h2>
                                            <dl class="transition-opacity duration-1500 delay-500 flex flex-wrap">
                                                <div class="px-4 sm:px-6 pb-6 dark:text-gray-400">
                                                    <dt class="inline">Booking Id: </dt>
                                                    <dd class="inline text-sm sm:text-base">
                                                        {{ $booking_id }}
                                                    </dd>
                                                </div>
                                                <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Service Location</dt>
                                                    <dd class="text-black dark:text-gray-100 text-sm sm:text-base"> {{ $s_address }}
                                                    </dd>
                                                </div>
                                                {{-- <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Distance</dt>
                                                    <dd class="text-black dark:text-gray-100 text-sm sm:text-base"> {{ $distance }} km
                                                    </dd>
                                                </div>
                                                <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Description</dt>
                                                    <dd class="text-sm sm:text-base">
                                                        Distance: {{ $distance }} km
                                                    </dd>
                                                </div> --}}
                                                <div class="w-full flex-none flex items-center px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">User Details</dt>
                                                    <dd class="text-sm sm:text-base font-medium dark:text-gray-300  dark:bg-gray-900 text-gray-700 bg-gray-100 rounded-full py-1 pl-2 pr-4 flex items-center">
                                                        <img class="h-10 w-auto" src="{{ $userRequest->user->avatar ? storage('storage/'.$userRequest->user->avatar) : asset('img/avatar.png') }}" alt="">&nbsp;&nbsp;{{ $userRequest->user->name }}
                                                    </dd>
                                                </div>
                                            </dl>
                                            <div class="grid grid-cols-2 gap-x-4 sm:gap-x-6 px-4 sm:px-6 py-4">
                                                {{-- Declining Request Button --}}
                                                <button class="text-base font-medium rounded-lg bg-gray-100 text-black py-3 text-center cursor-pointer" wire:loading.remove wire:target="rejectRequest" wire:click="rejectRequest()">
                                                    Decline
                                                </button>
                                                <button class="text-base font-medium rounded-lg bg-gray-100 text-black py-3 text-center cursor-pointer" wire:loading wire:target="rejectRequest">
                                                    <x-wait></x-wait> Cancelling Request
                                                </button>
                                                
                                                {{-- Accepting Request Buttons --}}
                                                <button class="text-base font-medium rounded-lg bg-red-500 text-white py-3 text-center cursor-pointer" wire:loading.remove wire:target="acceptRequest" wire:click="acceptRequest()">
                                                    {{-- Timer --}}
                                                    Accept (<span wire:ignore id="timer" class="text-sm text-white"></span> seconds)
                                                </button>
                                                <button class="text-base font-medium rounded-lg bg-red-500 text-white py-3 text-center cursor-pointer" wire:loading wire:target="acceptRequest">
                                                    <x-wait></x-wait> Accepting
                                                </button>
                                            </div>
                                        </article>
                                    </div>
                                </div>
                            @elseif(strcmp($status, 'STARTED') == 0)
                                {{-- Request PickUp Cancel --}}
                                <div class="p-4 col-span-4 lg:col-span-2 flex justify-center items-center">
                                    <div class="w-full relative">
                                        <article class="dark:text-gray-300">
                                            <h2 class="text-center transition-opacity duration-1500 delay-500 text-xl sm:text-3xl font-semibold text-black dark:text-gray-100 px-4 py-6 sm:px-6 pb-1">Ride Details</h2>
                                            <a class="bg-indigo-500 rounded-full h-10 w-10 flex justify-center cursor-pointer items-center top-5 text-lg right-16 absolute" href="tel:{{ $userRequest->provider->mobile }}">
                                                <i class="fa text-white fa-phone"></i>
                                            </a>
                                            <div class="bg-red-500 rounded-full h-10 w-10 flex justify-center cursor-pointer items-center top-5 text-lg right-5 absolute" x-on:click="toggleChatMenu()">
                                                <i class="fa text-white fa-comment"></i>
                                            </div>
                                            <dl class="transition-opacity duration-1500 delay-500 flex flex-wrap">
                                                <div class="w-full text-sm px-4 sm:px-6 pb-6 text-black dark:text-gray-100 text-center">
                                                    Booking Id:- {{ $booking_id }}
                                                </div>
                                                <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Service Location</dt>
                                                    <dd class="text-black dark:text-gray-100 text-sm sm:text-base"> {{ $s_address }}
                                                    </dd>
                                                </div>
                                                {{-- <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Distance</dt>
                                                    <dd class="text-black dark:text-gray-100 text-sm sm:text-base"> {{ $distance }} km
                                                    </dd>
                                                </div> --}}
                                                {{-- <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Description</dt>
                                                    <dd class="text-sm sm:text-base">
                                                        Distance: {{ $distance }} km
                                                    </dd>
                                                </div> --}}
                                                <div class="w-full flex-none flex items-center px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">User Details</dt>
                                                    <dd class="text-sm sm:text-base font-medium dark:text-gray-300 dark:bg-gray-900 text-gray-700 bg-gray-100 rounded-full py-1 pl-2 pr-4 flex items-center">
                                                        <img class="h-10 w-auto" src="{{ $userRequest->user->avatar ? storage('storage/'.$userRequest->user->avatar) : asset('img/avatar.png') }}" alt="">&nbsp;&nbsp;{{ $userRequest->user->name }}
                                                    </dd>
                                                </div>
                                            </dl>
                                            <div class="grid grid-cols-2 gap-x-4 sm:gap-x-6 px-4 sm:px-6 py-4">
                                                {{-- Declining Request Button --}}
                                                <button class="text-base font-medium rounded-lg bg-gray-100 text-black py-3 text-center cursor-pointer" wire:loading.remove wire:target="cancelRequestForm" wire:click="cancelRequestForm()">
                                                    Cancel
                                                </button>
                                                <button class="text-base font-medium rounded-lg bg-gray-100 text-black py-3 text-center cursor-pointer" wire:loading wire:target="cancelRequestForm">
                                                    <x-wait></x-wait> Cancelling Request
                                                </button>
                                                
                                                {{-- Accepting Request Buttons --}}
                                                <button class="text-base font-medium rounded-lg bg-red-500 text-white py-3 text-center cursor-pointer" wire:loading.remove wire:target="updateRequestStatus" wire:click="updateRequestStatus()">
                                                    {{-- Timer --}}
                                                    Arrived
                                                </button>
                                                <button class="text-base font-medium rounded-lg bg-red-500 text-white py-3 text-center cursor-pointer" wire:loading wire:target="updateRequestStatus">
                                                    <x-wait></x-wait> Changing Status
                                                </button>
                                            </div>
                                        </article>
                                    </div>
                                </div>
                            @elseif(strcmp($status, 'ARRIVED') == 0)
                                <div class="p-4 col-span-4 lg:col-span-2 flex justify-center items-center">
                                    <div class="w-full relative">
                                        <article class="dark:text-gray-300">
                                            <h2 class="transition-opacity duration-1500 delay-500 text-xl sm:text-2xl font-semibold text-black dark:text-gray-100 px-4 py-6 sm:px-6 pb-1">Ride Details ({{ $status }})</h2>
                                            
                                            {{-- Phone & Chat Buttons --}}
                                            <a class="bg-indigo-500 rounded-full h-10 w-10 flex justify-center cursor-pointer items-center top-5 text-lg right-16 absolute" href="tel:{{ $userRequest->provider->mobile }}">
                                                <i class="fa text-white fa-phone"></i>
                                            </a>
                                            <div class="bg-red-500 rounded-full h-10 w-10 flex justify-center cursor-pointer items-center top-5 text-lg right-5 absolute" x-on:click="toggleChatMenu()">
                                                <i class="fa text-white fa-comment"></i>
                                            </div>
                                            {{-- Buttons end here --}}

                                            <dl class="transition-opacity duration-1500 delay-500 flex flex-wrap">
                                                <div class="px-4 sm:px-6 pb-6">
                                                    <dt class="inline">Booking Id: </dt>
                                                    <dd class="inline text-sm sm:text-base">
                                                        {{ $booking_id }}
                                                    </dd>
                                                </div>
                                                <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Service Location</dt>
                                                    <dd class="text-black dark:text-gray-100 text-sm sm:text-base"> {{ $s_address }}
                                                    </dd>
                                                </div>
                                                {{-- <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Drop Location</dt>
                                                    <dd class="text-black dark:text-gray-100 text-sm sm:text-base"> {{ $d_address }}
                                                    </dd>
                                                </div>
                                                <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Distance</dt>
                                                    <dd class="text-black dark:text-gray-100 text-sm sm:text-base"> {{ $distance }} km
                                                    </dd>
                                                </div> --}}
                                                {{-- <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Description</dt>
                                                    <dd class="text-sm sm:text-base">
                                                        Distance: {{ $distance }} km
                                                    </dd>
                                                </div> --}}
                                                <div class="w-full flex-none flex items-center px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">User Details</dt>
                                                    <dd class="text-sm sm:text-base font-medium dark:text-gray-300  dark:bg-gray-900 text-gray-700 bg-gray-100 rounded-full py-1 pl-2 pr-4 flex items-center">
                                                        <img class="h-10 w-auto" src="{{ $userRequest->user->avatar ? storage('storage/'.$userRequest->user->avatar) : asset('img/avatar.png') }}" alt="">&nbsp;&nbsp;{{ $userRequest->user->name }}
                                                    </dd>
                                                </div>
                                            </dl>
                                            <div class="grid grid-cols-2 gap-x-4 sm:gap-x-6 px-4 sm:px-6 py-4">
                                                {{-- Declining Request Button --}}
                                                {{-- Declining Request Button --}}
                                                <button class="text-base font-medium rounded-lg bg-gray-100 text-black py-3 text-center cursor-pointer" wire:loading.remove wire:target="cancelRequestForm" wire:click="cancelRequestForm()">
                                                    Cancel
                                                </button>
                                                <button class="text-base font-medium rounded-lg bg-gray-100 text-black py-3 text-center cursor-pointer" wire:loading wire:target="cancelRequestForm">
                                                    <x-wait></x-wait> Cancelling Request
                                                </button>
                                                
                                                {{-- Accepting Request Buttons --}}
                                                <button class="text-base font-medium rounded-lg bg-red-500 text-white py-3 text-center cursor-pointer" wire:loading.remove wire:target="updateRequestStatus" wire:click="updateRequestStatus()">
                                                    {{-- Timer --}}
                                                    Start Service
                                                </button>
                                            </div>
                                        </article>                        
                                    </div>
                                </div>
                            @elseif(strcmp($status, 'PICKEDUP') == 0)
                                <div class="p-4 col-span-4 lg:col-span-2 flex justify-center items-center">
                                    <div class="w-full relative">
                                        <article class="dark:text-gray-300">
                                            <h2 class="transition-opacity duration-1500 delay-500 text-xl sm:text-2xl font-semibold text-black dark:text-gray-100 px-4 py-6 sm:px-6 pb-1">Ride Details ({{ $status }})</h2>
                                            <dl class="transition-opacity duration-1500 delay-500 flex flex-wrap">
                                                <div class="px-4 sm:px-6 pb-6">
                                                    <dt class="inline">Booking Id: </dt>
                                                    <dd class="inline text-sm sm:text-base">
                                                        {{ $booking_id }}
                                                    </dd>
                                                </div>
                                                <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Service Location</dt>
                                                    <dd class="text-black dark:text-gray-100 text-sm sm:text-base"> {{ $s_address }}
                                                    </dd>
                                                </div>
                                                {{-- <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Drop Location</dt>
                                                    <dd class="text-black dark:text-gray-100 text-sm sm:text-base"> {{ $d_address }}
                                                    </dd>
                                                </div>
                                                <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Distance</dt>
                                                    <dd class="text-black dark:text-gray-100 text-sm sm:text-base"> {{ $distance }} km
                                                    </dd>
                                                </div> --}}
                                                {{-- <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Description</dt>
                                                    <dd class="text-sm sm:text-base">
                                                        Distance: {{ $distance }} km
                                                    </dd>
                                                </div> --}}
                                                <div class="w-full flex-none flex items-center px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">User Details</dt>
                                                    <dd class="text-sm sm:text-base font-medium dark:text-gray-300  dark:bg-gray-900 text-gray-700 bg-gray-100 rounded-full py-1 pl-2 pr-4 flex items-center">
                                                        <img class="h-10 w-auto" src="{{ $userRequest->user->avatar ? storage('storage/'.$userRequest->user->avatar) : asset('img/avatar.png') }}" alt="">&nbsp;&nbsp;{{ $userRequest->user->name }}
                                                    </dd>
                                                </div>
                                            </dl>
                                            <div class="px-4 sm:px-6 py-4 text-right">
                                                {{-- Accepting Request Buttons --}}
                                                <button class="sm:w-1/2 w-full text-base font-medium rounded-lg bg-indigo-500 text-white py-3 text-center cursor-pointer" wire:loading.remove wire:target="updateRequestStatus" wire:click="updateRequestStatus()" wire:loading.remove>
                                                    {{-- Timer --}}
                                                    Service Completed
                                                </button>
                                                <button class="sm:w-1/2 w-full text-base font-medium rounded-lg bg-indigo-500 text-white py-3 text-center cursor-pointer" wire:loading.remove wire:target="updateRequestStatus" wire:loading>
                                                    {{-- Timer --}}
                                                    <x-wait></x-wait> Changing Status
                                                </button>
                                            </div>
                                        </article>                        
                                    </div>
                                </div>
                            @elseif(strcmp($status, 'DROPPED') == 0)
                                <div class="p-4 col-span-4 flex justify-center items-center">
                                    <div class="w-full relative">
                                        <article class="dark:text-gray-300">
                                            <h2 class="transition-opacity duration-1500 delay-500 text-xl sm:text-2xl font-semibold text-black dark:text-gray-100 px-4 py-6 sm:px-6 pb-1">Ride Details ({{ $status }})</h2>
                                            <dl class="transition-opacity duration-1500 delay-500 flex flex-wrap">
                                                <div class="px-4 sm:px-6 mb-1 pb-2 w-full border-b dark:border-gray-500">
                                                    <dt class="inline">Booking Id: </dt>
                                                    <dd class="inline text-sm sm:text-base">
                                                        {{ $booking_id }}
                                                    </dd>
                                                </div>
                                                <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Service Location</dt>
                                                    <dd class="text-black dark:text-gray-100 text-sm sm:text-base"> {{ $s_address }}
                                                    </dd>
                                                </div>
                                                {{-- <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Drop Location</dt>
                                                    <dd class="text-black dark:text-gray-100 text-sm sm:text-base"> {{ $d_address }}
                                                    </dd>
                                                </div>
                                                <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Distance</dt>
                                                    <dd class="text-black dark:text-gray-100 text-sm sm:text-base"> {{ $distance }} km
                                                    </dd>
                                                </div> --}}
                                                <div class="w-full flex-none flex items-baseline px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">Amount To Be Paid: </dt>
                                                    <dd class="text-black dark:text-gray-100 text-sm sm:text-base">
                                                        {{ currency($payment_amount) }}
                                                    </dd>
                                                </div>
                                                <div class="w-full flex-none flex items-center px-4 sm:px-6 py-4">
                                                    <dt class="text-gray-700 dark:text-gray-300 w-2/5 sm:w-1/3 flex-none uppercase text-xs sm:text-sm font-semibold tracking-wide">User Details</dt>
                                                    <dd class="text-sm sm:text-base font-medium dark:text-gray-300  dark:bg-gray-900 text-gray-700 bg-gray-100 rounded-full py-1 pl-2 pr-4 flex items-center">
                                                        <img class="h-10 w-auto" src="{{ $userRequest->user->avatar ? storage('storage/'.$userRequest->user->avatar) : asset('img/avatar.png') }}" alt="">&nbsp;&nbsp;{{ $userRequest->user->name }}
                                                    </dd>
                                                </div>
                                            </dl>
                                            <div class="px-4 sm:px-6 py-4 text-right">
                                                {{-- Accepting Request Buttons --}}
                                                <button class="px-4 text-base font-medium rounded-lg bg-indigo-500 text-white py-3 text-center cursor-pointer" wire:loading.remove wire:target="updateRequestStatus" wire:click="updateRequestStatus()" wire:loading.remove>
                                                    Confirm Payment
                                                </button>
                                                <button class="px-4 text-base font-medium rounded-lg bg-indigo-500 text-white py-3 text-center cursor-pointer" wire:loading.remove wire:target="updateRequestStatus" wire:loading>
                                                    <x-wait></x-wait> Confirming Payment
                                                </button> 
                                            </div>
                                        </article>                        
                                    </div>
                                </div>
                            @elseif(strcmp($status, 'COMPLETED')  == 0)
                                <div class="p-4 col-span-4 flex justify-center items-center">
                                    <div class="w-full relative">
                                        <article class="dark:text-gray-300">
                                            <h2 class="text-center transition-opacity duration-1500 delay-500 text-xl sm:text-3xl font-semibold text-black dark:text-gray-100 px-4 py-6 sm:px-6 pb-2">Rate Your Ride</h2>
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
                                                    <button type="submit" class="inline-block py-2 px-5 mt-4 rounded text-sm bg-blue-500 hover:bg-blue-600 text-blue-100" wire:target="saveRating" type="submit" wire:loading.remove>
                                                        Submit
                                                    </button>
                                                    <button type="submit" class="inline-block py-2 px-5 mt-4 rounded text-sm bg-blue-500 hover:bg-blue-600 text-blue-100" wire:target="saveRating" type="button" wire:loading>
                                                        <x-wait></x-wait> Submitting Response
                                                    </button>
                                                </div>
                                            </x-form>
                                        </article>
                                    </div>
                                </div>
                            @elseif(strcmp($status, 'CANCEL') == 0)
                                <div class="p-4 col-span-4 flex justify-center items-center h-full">
                                    <div class="w-full relative h-full">
                                        <article class="bg-white text-gray-600 dark:bg-gray-800 lg:w-full w-full p-10 mx-auto leading-6 rounded">
                                            <h2 class="text-center transition-opacity duration-1500 delay-500 text-xl sm:text-3xl font-semibold text-black dark:text-gray-100 px-4 py-6 sm:px-6 pb-2">Cancel Ride <span class="text-gray-700 dark:text-gray-300 text-sm">(Select Reason)</span></h2>
                                            @forelse ($cancelReasons as $reason)
                                                <div class="flex items-center flex-wrap py-2 sm:px-6">
                                                    <input required wire:model.defer="selectedReason" type="radio" name="cancel_reason" id="reason{{$reason->id}}" value="{{ $reason->id }}" class="inline">
                                                    <label class="text-black dark:text-gray-200 ml-2" for="reason{{$reason->id}}">{{ $reason->reason }}</label>
                                                </div>
                                            @empty
                                                {{-- <dl class="transition-opacity duration-1500 delay-500 flex flex-wrap">
                                                    <div class="px-4 sm:px-6 pb-6">
                                                        <dt class="text-black dark:text-gray-100 inline">{{ $cancelReason->name }}</dt>
                                                    </div>
                                                </dl> --}}
                                            @endforelse
                                            <div class="grid grid-cols-2 gap-x-4 sm:gap-x-6 px-4 sm:px-6 py-4">
                                                {{-- Declining Request Button --}}
                                                <button class="text-base font-medium rounded-lg bg-gray-100 text-black py-3 text-center cursor-pointer" wire:loading.remove wire:target="cancelRequestCancel" wire:click="cancelRequestCancel()">
                                                    Cancel
                                                </button>
                                                <button class="text-base font-medium rounded-lg bg-gray-100 text-black py-3 text-center cursor-pointer" wire:loading wire:target="cancelRequestCancel">
                                                    <x-wait></x-wait> Resuming Uncompleted Request
                                                </button>
                                                
                                                {{-- Accepting Request Buttons --}}
                                                <button class="text-base font-medium rounded-lg bg-red-500 text-white py-3 text-center cursor-pointer" wire:loading.remove wire:target="cancelRequest" wire:click="cancelRequest()">
                                                    {{-- Timer --}}
                                                    Submit
                                                </button>
                                                <button class="text-base font-medium rounded-lg bg-red-500 text-white py-3 text-center cursor-pointer" wire:loading wire:target="cancelRequest">
                                                    <x-wait></x-wait> Submitting Response
                                                </button>
                                            </div>
                                        </article>
                                    </div>
                                </div>
                            @endif
                            @if(in_array($status, ['STARTED', 'ARRIVED']))
                                <div x-show.transition.origin.bottom="showChat" class="absolute top-0 left-0 h-full w-full dark:bg-gray-900 bg-white" wire:poll>
                                    <div class="flex items-center justify-between border-b px-2 border-gray-200 dark:border-gray-600" style="height: 10%;">
                                        <span class="flex dark:text-gray-300 items-center">
                                            <img class="border border-gray-100 dark:border-gray-900 rounded-full h-12 w-12" src="{{ $userRequest->provider->avatar ? asset('storage/'.$userRequest->provider->avatar) : asset('img/avatar.png') }}" alt="">&nbsp;{{ $userRequest->provider->name }}
                                        </span>
                                        <span class="bg-red-500 rounded-full h-8 w-8 flex justify-center cursor-pointer items-center" x-on:click="toggleChatMenu()">
                                            <i class="fa text-white fa-times"></i>
                                        </span>
                                    </div>
                                    <div class="relative h-full w-full justify-center" style="height: 90%;">
                                        <div class="border-l border-r h-full  dark:border-gray-900 overflow-y-scroll" style="height: 92%" id="messageContainerBody">
                                            {{-- 
                                                TODO: make cursor pointer and show time and date of message.
                                            --}}
                                            <style>
                                                .received:before { position: absolute; right: -14px; content:""; border-top: 7px solid white; border-bottom: 7px solid white; border-left: 7px solid #60a5fa; border-right: 7px solid white;}
                                                .sent:before { position: absolute; left: -14px; content:""; border-top: 7px solid white; border-bottom: 7px solid white; border-left: 7px solid white; border-right: 7px solid #f3f4f6;}
                                                /* Dark MOdes */
                                                .dark .dark\:sent:before {position: absolute; left: -14px; content:""; border-top: 7px solid #111827; border-bottom: 7px solid #111827; border-left: 7px solid #111827; border-right: 7px solid #f3f4f6;}
                                                .dark .dark\:received:before { position: absolute; right: -14px; content:""; border-top: 7px solid #111827; border-bottom: 7px solid #111827; border-left: 7px solid #60a5fa; border-right: 7px solid #111827;}
                                            </style>
                                            <div class="relative p-5" id="messageContainer">
                                                @if($chat && $chat->count())
                                                    @forelse ($chat as $msg)
                                                        @if($msg->type == 'pu')
                                                            <div class="flex justify-end @if(!$loop->first) mt-5 @endif">
                                                                <div class="flex justify-center items-top" style="max-width: 80%; width: fit-content;">
                                                                    <span class="relative dark:received received bg-blue-400 text-white rounded mr-5 p-3 cursor-pointer" title="{{ $msg->created_at->diffForHumans() }}">
                                                                        {{ $msg->message }}
                                                                    </span>

                                                                    <img class="border border-gray-100 rounded-full h-12 w-12" src="{{ $userRequest->provider->avatar ? asset('storage/'.$userRequest->provider->avatar) : asset('img/avatar.png') }}" alt="">
                                                                </div>
                                                            </div>
                                                        @elseif($msg->type == 'up')
                                                            <div class="flex @if(!$loop->first) mt-5 @endif">
                                                                <div class="flex justify-center items-top" style="max-width: 80%; width: fit-content;">
                                                                    <img class="border border-gray-50 rounded-full h-12 w-12" src="{{ $userRequest->user->avatar ? asset('storage/'.$userRequest->user->avatar) : asset('img/avatar.png') }}" alt="">

                                                                    <span class="relative sent dark:sent bg-gray-100 rounded ml-5 p-3 cursor-pointer" title="{{ $msg->created_at->diffForHumans() }}">
                                                                        {{ $msg->message }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @empty
                                                        <span class="dark:text-gray-300">
                                                            No Chat Yet.
                                                        </span>
                                                    @endforelse
                                                @else
                                                    <span class="dark:text-gray-300">
                                                        No Chat Yet.
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <input type="text" class="absolute bottom-0 px-2 focus:outline-none left-0 w-full border border-r-0 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-800" style="height: 8%; z-index: 10; width: 95%;" wire:model.defer="message" wire:keydown.enter="sendMessage()" autofocus>
                                        <div class="bg-red-500 absolute flex justify-center items-center right-0 bottom-0" style="z-index: 11; height: 8%; width:5%;">
                                            <i class="fa fa-paper-plane text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </article>
                    </div>
                </div>
            </div>
        @else
            <button class="relative align-middle rounded-md focus:outline-none focus:shadow-outline-purple" x-on:click="noIncomingRequest=!noIncomingRequest" title="Incoming Request" wire:key="NoRequestButton">
                <i class="fa text-gray-400 dark:text-gray-300 fa-car"></i>
            </button>
            <div x-show="noIncomingRequest" wire:key="NoRequestMessage">
                <ul
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"                                                                
                    x-transition:leave-end="opacity-0"
                    x-on:click.away="noIncomingRequest=false"
                    @keydown.escape="noIncomingRequest=false"
                    class="absolute right-0 w-56 mt-2 space-y-2 text-gray-600 bg-white border border-gray-100 rounded-md shadow-md dark:text-gray-300 dark:border-gray-700 dark:bg-gray-700">
                    <li class="flex transition-colors py-2 px-3 duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                        There Are No Incoming Requests Right Now.
                    </li>
                </ul>
            </div>
        @endif
    </div>
</div>