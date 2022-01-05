<div>
    <script>
        function alpineNotif()
        {
            return {
                showNotification: false,
                toggleNotif: function() {
                    this.showNotification = !this.showNotification;
                }
            }
        }
    </script>
    <div x-data="alpineNotif()" x-cloak>
        <button class="relative align-middle rounded-md focus:outline-none focus:shadow-outline-purple" x-on:click="toggleNotif()" title="Incoming Request">
            <i class="fa text-gray-400 dark:text-gray-300 fa-bell"></i>
        </button>
        <div x-show="showNotification" wire:key="NoRequestMessage" class="relative">
            <ul
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-on:click.away="showNotification=false"
                @keydown.escape="showNotification=false"
                class="absolute right-0 w-96 h-96 overflow-y-scroll mt-2 text-gray-600 bg-white border border-gray-100 rounded-md shadow-md dark:text-gray-300 dark:border-gray-700 dark:bg-gray-700">
                @forelse($notifications as $notification)
                    <li class="">
                        <a href="{{ $userType == 'user' ? route('user.notification').'?notificationId='.$notification->id : route('provider.notification').'?notificationId='.$notification->id }}" class="flex justify-start py-3 px-3 transition-colors duration-150 hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200 border-b">
                            <div class="w-10 h-10 mr-2 border rounded-full bg-center bg-cover" style="background-image: url('{{ asset('storage/'.$notification->image) }}');">
                                {{-- <img src="" alt="" class="w-10 h-10 mr-2 border rounded-full"> --}}
                            </div>
                            <div class="flex-grow">
                                <p class="w-full leading-4 mt-1">
                                    {{ Str::limit($notification->description, 40) ?? '-' }}
                                </p>
                                <span class="w-full text-xs float-right text-right mt-2">
                                    {{ $notification->created_at->toDate()->format('d/m/y') }}
                                </span>
                            </div>
                        </a>
                    </li>
                    @if($loop->last)
                        <li class="flex justify-center transition-colors duration-150 hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                            <a class="text-sm py-2 flex-grow text-center" href="{{ route('user.notification') }}">Show All Notification</a>
                        </li>
                    @endif
                @empty
                    <li class="flex transition-colors py-2 px-3 duration-150 hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                        No notifications yet.
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
