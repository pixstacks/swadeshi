<div class="w-full" style="height: 80vh;">
    {{-- Big Screen View --}}
    <div class="md:flex h-full w-full hidden">
        <div class="md:w-1/3 w-full h-full border  dark:border-gray-700 overflow-y-scroll ">
            @forelse ($notifications as $notification)
                <div class="flex justify-start transition-colors py-3 px-3 duration-150 opacity-80 hover:bg-gray-100 hover:text-gray-800 dark:bg-gray-800 dark:hover:bg-gray-900 dark:hover:text-gray-200 border-b dark:border-gray-600 cursor-pointer" 
                    wire:click="getNotification({{$notification->id}})"
                >
                    <div class="w-12">
                        <img src="{{ asset('storage/'.$notification->image) }}" alt="" class="w-10 h-10 mr-2 border rounded-full">
                    </div>
                    <div class="flex-grow dark:text-gray-100">
                        <p class="w-full leading-4 mt-1">
                            {{ Str::limit($notification->description, 70) ?? '-' }}
                        </p>
                        <span class="w-full text-xs float-right text-right mt-2 dark:text-gray-100">
                            {{ $notification->created_at->toDate()->format('d/m/y') }}
                        </span>
                    </div>
                </div>
                @if ($loop->last && $haveMoreResults)
                    <li class="flex justify-center transition-colors duration-150 bg-gray-100 hover:bg-gray-100 dark:bg-gray-700 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200 cursor-pointer">
                        <a class="flex-grow text-center text-sm py-2 dark:text-gray-300" wire:click="loadMoreNotification()" wire:loading.remove>
                            Show All Notification
                        </a>
                        <a wire:loading wire:target="loadMoreNotification" class="flex-grow text-center text-sm py-2 dark:text-gray-300">
                            <i class="fa fa-spin fa-refresh mr-1"></i>Loading Notifications
                        </a>
                    </li>
                @endif
            @empty
                No Notifications yet.
            @endforelse
        </div>
        <div  class="md:w-2/3 w-full h-full flex items-center justify-center border-t border-b dark:border-gray-700">
            <div class="flex w-full h-full justify-center overflow-y-scroll items-center" wire:target="getNotification" wire:loading.remove>
                @if ($currNotification)
                    <div class="w-full text-center p-8 h-full">
                        <img class="mx-auto max-w-full border  dark:border-gray-700 p-1" src="{{ asset('storage/'.$currNotification->image) }}" alt="" style="max-height: 400px;">
                        <br>
                        <span class="dark:text-gray-300">
                            {{ $currNotification->description }}
                        </span>
                    </div>
                @else
                    No Notification Selected.
                @endif
            </div>
            {{-- Loading modal --}}
            <div wire:target="getNotification" wire:loading class="flex justify-center items-center w-full h-full">
                <div class="w-full relative text-center h-full flex justify-center items-center">
                    <i class="fa fa-refresh text-blue-900 dark:text-blue-200 fa-2x fa-spin"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile Screen View --}}
    <div class="md:hidden h-full w-full">
        {{-- Available Notification Modal --}}
        @if(!$currNotification)
            <div class="w-full h-full border  dark:border-gray-700 overflow-y-scroll" wire:loading.remove wire:target="getNotification,notificationList">
                @forelse ($notifications as $notification)
                    <div class="flex justify-start transition-colors py-3 px-3 duration-150 hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200 border-b cursor-pointer" 
                        wire:click="getNotification({{$notification->id}})"
                    >
                        <div class="w-12">
                            <img src="{{ asset('storage/'.$notification->image) }}" alt="" class="w-10 h-10 mr-2 border rounded-full">
                        </div>
                        <div class="flex-grow">
                            <p class="w-full leading-4 mt-1">
                                {{ Str::limit($notification->description, 70) ?? '-' }}
                            </p>
                            <span class="w-full text-xs float-right text-right mt-2">
                                {{ $notification->created_at->toDate()->format('d/m/y') }}
                            </span>
                        </div>
                    </div>
                    @if ($loop->last && $haveMoreResults)
                        <li class="flex justify-center transition-colors duration-150 bg-gray-100 hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200 cursor-pointer">
                            <a class="flex-grow text-center text-sm py-2" wire:click="loadMoreNotification()" wire:loading.remove>
                                Show All Notification
                            </a>
                            <a wire:loading wire:target="loadMoreNotification" class="flex-grow text-center text-sm py-2">
                                <i class="fa fa-spin fa-refresh mr-1"></i>Loading Notifications
                            </a>
                        </li>
                    @endif
                @empty
                    No Notifications yet.
                @endforelse
            </div>
        @else
            <div class="w-full h-full flex flex-col items-center justify-center border-t border-b" wire:loading.remove wire:target="getNotification,notificationList">
                {{-- Showing Notification Modal --}}
                <div class="h-10 flex items-center w-full px-3 border-b">
                    <i class="fa fa-arrow-left cursor-pointer" wire:click="notificationList()"></i>
                </div>
                <div class="flex w-full h-full justify-center overflow-y-scroll items-center">
                    @if ($currNotification)
                        <div class="w-full text-center p-8 h-full">
                            <img class="mx-auto max-w-full border p-1" src="{{ asset('storage/'.$currNotification->image) }}" alt="" style="max-height: 400px;">
                            <br>
                            <span>
                                {{ $currNotification->description }}
                            </span>
                        </div>
                    @else
                        No Notification Selected.
                    @endif
                </div>
            </div>
        @endif
        <div wire:target="getNotification,notificationList" wire:loading class="flex justify-center items-center w-full h-full border-t border-b">
            <div class="w-full relative text-center h-full flex justify-center items-center">
                <i class="fa fa-refresh text-blue-900 dark:text-blue-200 fa-2x fa-spin"></i>
            </div>
        </div>
    </div>
</div>
