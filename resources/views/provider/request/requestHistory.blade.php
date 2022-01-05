@extends('provider.layout.app')

@section('title')
    Provider {{ __('crud.navlinks.request') }} {{ __('crud.navlinks.history') }}
@endsection

@section('heading')
    {{ __('crud.navlinks.request') }} {{ __('crud.navlinks.history') }}
@endsection

@section('content')
    <section class="px-4 sm:px-6 lg:px-4 xl:px-6 pt-4 pb-4 sm:pb-6 lg:pb-4 xl:pb-6 space-y-4">
        <ul class="grid grid-cols-1 @if($requests->count() != 0) sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 @endif gap-4">
            @forelse ($requests as $request)
                <li class="hover:bg-light-blue-500 hover:border-transparent hover:shadow-lg group block rounded-lg border border-gray-200 dark:border-gray-700 dark:hover:border-trasparent dark:hover:shadow-lg dark:bg-gray-800">
                    <dl class="grid sm:block lg:grid xl:block grid-cols-2 grid-rows-2 items-center">
                        <div class="text-sm px-4 pt-4 font-medium sm:mb-4 lg:mb-0 xl:mb-2 dark:text-gray-400 text-justify">
                            <span class="dark:text-gray-100">To - </span>
                            {{ Str::limit($request->d_address, 50) }}
                        </div>
                        <div class="text-sm px-4 font-medium dark:text-gray-400 mt-1 text-right dark:border-gray-600 mb-4 border-b pb-2">
                            - On {{ $request->created_at->toDate()->format('d/M/Y') }} at {{ date('h:i a', strtotime($request->created_at)) }}
                        </div>
                        {{-- <hr class=""> --}}
                        <div class="text-sm px-4 font-medium sm:mb-4 lg:mb-0 xl:mb-4 dark:text-gray-400 mt-1 grid grid-cols-2">
                            <span class="dark:text-gray-100">Service Type :  </span>
                            {{ $request->serviceType->name }}
                        </div>
                        <div class="text-sm px-4 font-medium sm:mb-4 lg:mb-0 xl:mb-4 dark:text-gray-400 mt-1 grid grid-cols-2">
                            <span class="dark:text-gray-100">Request Status :  </span>
                            {{ ucfirst(strtolower($request->status)) }}
                        </div>
                        <div class="text-sm px-4 pb-4 font-medium dark:text-gray-400 mt-1 grid grid-cols-2">
                            @if($request->status == 'COMPLETED')
                                <span class="dark:text-gray-100">Earnings :  </span>
                                    {{ currency($request->payment->total ?? 0) ?? '-' }}
                            @elseif($request->status == 'CANCELLED')
                                <span class="dark:text-gray-100">Cancel By :  </span>
                                {{ $request->cancelled_by ? ( $request->cancelled_by == 'USER' ? 'User' : 'You') : '--' }}
                            @endif
                        </div>
                        <div class="text-right p-4 pt-0">
                            <a href="{{ route('provider.showRequest', $request->id) }}" class="bg-blue-500 focus:outline-none hover:bg-blue-700 text-sm text-white py-2 px-4 rounded-full">
                                View Full Request
                            </a>
                        </div>
                        {{-- @if($request->serviceType->image)
                            <div class="col-start-2 row-start-1 row-end-3">
                                <span class="flex justify-end sm:justify-start lg:justify-end xl:justify-start -space-x-2">
                                    <img src="{{ asset('storage/'.$request->serviceType->image) }}" class="rounded-full bg-gray-100 border-2 border-white" />
                                </span>
                            </div>
                        @endif --}}
                    </dl>
                </li>
            @empty
                <li class="hover:bg-light-blue-500 hover:border-transparent hover:shadow-lg group block rounded-lg border border-gray-200 dark:border-gray-700 dark:hover:border-trasparent dark:hover:shadow-lg p-3 text-center w-full dark:text-gray-300">
                    @lang('crud.admin.userRequests.not_found')
                </li>
            @endforelse
        </ul>
        <div class="">
            {!! $requests->links() !!}
        </div>
    </section>
@endsection