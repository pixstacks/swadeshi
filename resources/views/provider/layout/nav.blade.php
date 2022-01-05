<div id="navigation" class="border shadow-md rounded duration-300 ease-in-out bg-transparent fixed py-2 dark:bg-gray-800 bg-white text-gray-600 dark:text-gray-300 dark:border-gray-700" style="z-index: 25; width: 98%; margin-left: 1%; margin-top: 10px;">
    <nav class="flex justify-between items-center px-4 xl:px-10">
        <a class="filter brightness-0 filter invert text-2xl leading-none" href="{{ route('home') }}">
            <img class="filter brightness-0 filter invert" src="{{ url('storage/'.config('constants.site_logo')) }}" alt="" width="auto" style="height: 3rem;">
        </a>
        
        @auth('provider')
            <ul class="lg:ml-auto lg:mr-6 lg:items-center lg:space-x-2 flex justify-center items-center">
                <li class="hidden lg:block">
                    <ul class="lg:ml-auto lg:items-center lg:space-x-12 flex justify-center items-center">
                        {{-- Show This in Big Screen Hide on smaller screens. On smaller screen show side menus. --}}
                        @include('provider.layout.nav_content')
                    </ul>
                </li>

                {{-- Part To show in all screens sizes. --}}
                <li class="flex px-4 h-10 justify-center items-center">
                    <button class="rounded-md focus:outline-none focus:shadow-outline-purple" @click="toggleTheme" aria-label="Toggle color mode">
                        <template x-if="!dark">
                            <svg class="text-gray-400 w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                        </template>
                        <template x-if="dark">
                            <svg class="dark:text-gray-200 w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                clip-rule="evenodd"
                            ></path>
                            </svg>
                        </template>
                    </button>
                </li>
                <li class="px-4 h-10 flex justify-center items-center">
                    @livewire('notification-bell', [
                        'userType' => 'provider'
                    ])
                </li>
                <li class="px-4 h-10 flex justify-center items-center">
                    @livewire('provider.incoming-request')
                </li>
                <li class="lg:hidden px-4 h-10 flex justify-center items-center">
                    <button class="navbar-burger duration-75 ease-in-out flex items-center rounded focus:outline-none relative top-px">
                        <svg class="block dark:text-white h-4 w-4" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                            <title>Mobile menu</title>
                            <path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z"></path>
                        </svg>
                    </button>
                </li>
            </ul>

            <div class="hidden lg:block" x-data="{isProfileMenuOpen : false}">
                <button class="align-middle rounded-full focus:shadow-outline-purple focus:outline-none" @click="isProfileMenuOpen=!isProfileMenuOpen" @click.away="isProfileMenuOpen=false" @keydown.escape="closeProfileMenu" aria-label="Account" aria-haspopup="true">
                    <img class="object-cover w-8 h-8 rounded-full" src="{{ auth('provider')->user()->avatar ? asset(auth('provider')->user()->avatar) : asset('img/avatar.png') }}" alt="" aria-hidden="true" />
                </button>
                <template x-if="isProfileMenuOpen">
                    <ul x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click.away="closeProfileMenu" @keydown.escape="closeProfileMenu" class="absolute right-0 w-56 p-2 mt-2 space-y-2 text-gray-600 bg-white border border-gray-100 rounded-md shadow-md dark:border-gray-700 dark:bg-gray-700" aria-label="submenu">
                        <li class="flex mt-0">
                            <a class="text-gray-600 inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md dark:text-gray-300 hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                href="{{ route('provider.wallet') }}">
                                <i class="fa fa-calendar-o mr-3"></i>
                                <span>Wallet</span>
                            </a>
                        </li>
                        <li class="flex mt-0">
                            <a class="text-gray-600 inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md dark:text-gray-300 hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                href="{{ route('provider.settings') }}">
                                <i class="fa fa-cog mr-3"></i>
                                <span>{{ __('crud.navlinks.setting') }}</span>
                            </a>
                        </li>
                        
                        <li class="flex mt-0">
                            <a class="text-gray-600 inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 dark:text-gray-300 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                href="{{ route('provider.logout') }}">
                                <i class="fa fa-sign-out mr-3"></i>
                                <span>Log out</span>
                            </a>
                        </li>
                    </ul>
                </template>
            </div>
        @endauth
    </nav>

    {{-- Mobile Side Menu --}}
    @auth('provider')
        <div class="hidden navbar-menu relative z-50">
            <div class="navbar-backdrop fixed inset-0 bg-gray-800 opacity-25"></div>
            <nav class="fixed top-0 left-0 bottom-0 flex flex-col w-5/6 max-w-sm py-6 px-6 bg-white dark:bg-gray-800 border-r overflow-y-auto dark:border-gray-700">
                <div class="flex items-center mb-8">
                    <a class="mr-auto text-lg font-semibold leading-none" href="{{ route('home') }}"><img class="h-7" src="{{ url('storage/'.config('constants.site_logo')) }}" alt="" width="auto"></a>
                    <button class="navbar-close">
                        <svg class="h-6 w-6 text-gray-500 cursor-pointer hover:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewbox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div>
                    <ul>
                        @include('provider.layout.nav_content')
                        <li class="mb-1" style="margin-top: 0px;">
                            <a class="block p-4 text-sm font-medium text-gray-900 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-indigo-100 hover:bg-gray-50 hover:text-indigo-500 rounded" href="{{ route('provider.wallet') }}">
                                Wallet
                            </a>
                        </li>
                        <li class="mb-1" style="margin-top: 0px;">
                            <a class="block p-4 text-sm font-medium text-gray-900 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-indigo-100 hover:bg-gray-50 hover:text-indigo-500 rounded" href="{{ route('provider.settings') }}">
                                {{ __('crud.navlinks.setting') }}
                            </a>
                        </li>
                        <li class="mb-1" style="margin-top: 0px;">
                            <a class="block p-4 text-sm font-medium text-gray-900 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-indigo-100 hover:bg-gray-50 hover:text-indigo-500 rounded" href="{{ route('provider.logout') }}">
                                Log out
                            </a>
                        </li>
                        {{-- <li class="mb-1"><a class="block p-4 text-sm font-medium text-gray-900 hover:bg-gray-50 hover:text-indigo-500 rounded" href="{{ route('user.activity') }}">Booking Activity</a></li> --}}
                    </ul>
                </div>
            </nav>
        </div>
    @endauth
</div>