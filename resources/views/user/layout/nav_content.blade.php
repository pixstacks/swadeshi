<li class="lg:flex mb-1"><a class="block p-4 text-sm font-medium text-gray-900 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-indigo-100 hover:bg-gray-50 hover:text-indigo-500 rounded" href="{{ route('home') }}">{{ __('crud.navlinks.home') }}</a></li>
@auth('web')
    <li class="lg:flex mb-1"><a class="block p-4 text-sm font-medium text-gray-900 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-indigo-100 hover:bg-gray-50 hover:text-indigo-500 rounded" href="{{ route('user.serviceCheckout') }}">Book A Ride</a></li>
    <li class="lg:flex mb-1"><a class="block p-4 text-sm font-medium text-gray-900 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-indigo-100 hover:bg-gray-50 hover:text-indigo-500 rounded" href="{{ route('user.wallet') }}">Wallet</a></li>
    <li class="lg:flex mb-1"><a class="block p-4 text-sm font-medium text-gray-900 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-indigo-100 hover:bg-gray-50 hover:text-indigo-500 rounded" href="{{ route('user.activity') }}">Booking Activity</a></li>
@endauth

@guest
    <li class="lg:flex mb-1"><a class="block p-4 text-sm font-medium text-gray-900 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-indigo-100 hover:bg-gray-50 hover:text-indigo-500 rounded" href="{{ route('ride') }}">Ride</a></li>
    <li class="lg:flex mb-1"><a class="block p-4 text-sm font-medium text-gray-900 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-indigo-100 hover:bg-gray-50 hover:text-indigo-500 rounded" href="{{ route('drive') }}">Driver</a></li>
    <li class="lg:flex mb-1"><a class="block p-4 text-sm font-medium text-gray-900 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-indigo-100 hover:bg-gray-50 hover:text-indigo-500 rounded" href="{{ route('faq') }}">Help</a></li>
@endguest