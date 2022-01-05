<div class="relative" x-data={searchResult:false}>
    <div x-on:click.away="searchResult=false">
        <input
            class="block w-full pr-20 mt-1 text-sm text-black border border-gray-200 rounded-l dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:outline-none dark:focus:shadow-outline-gray"
            placeholder="Enter Search Value" style="padding: 8px;" wire:model="search" x-on:focus="searchResult='true'">
        <ul class="absolute w-full bg-white border-b border-l border-r divide-y dark:divide-gray-700 dark:bg-gray-900 dark:text-gray-500 dark:border-gray-700"
            x-show="searchResult" style="max-height: 200px; overflow-y: scroll;">
            <li class="p-3 text-center dark:bg-gray-900 dark:text-gray-500" wire:loading>
                <i class="fa-spinner fa-pulse fa"></i>
            </li>
            @forelse ($results as $result)
            {{--
                For case when there are sub services. 
                @if($result->children->count())
                    @continue
                @endif 
            --}}
            <li class="p-3 dark:bg-gray-900 dark:text-gray-500" wire:loading.remove>
                <div class="flex items-center justify-between">
                    <span>{{ $result->name }}</span>
                    <button class="w-8 h-8 mx-1 text-white bg-green-400 rounded-full hover:bg-green-500"
                        wire:click="addService({{$result->id}})">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </li>
            @empty
            <li class="p-3 dark:bg-gray-900 dark:text-gray-500" wire:loading.remove>
                No Search Results Found
            </li>
            @endforelse
        </ul>
    </div>

    <h4 class="my-4 font-semibold text-gray-800 dark:text-gray-300">
        Skills
    </h4>
    <table class="w-full whitespace-no-wrap border dark:border-gray-700">
        <thead>
            <tr
                class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                <th class="px-4 py-3 text-center">{{ __('crud.inputs.SNo') }}</th>
                <th class="px-4 py-3 text-center">{{ __('crud.admin.serviceTypes.name') }}</th>
                <th class="px-4 py-3 text-center">{{ __('crud.inputs.number_plate') }}</th>
                <th class="px-4 py-3 text-center">{{ __('crud.inputs.model') }}</th>
                <th class="px-4 py-3 text-center">{{ __('crud.general.actions') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
            {{-- Approved --}}
            @forelse ($provider->services as $service)
                <livewire:provider-single-service :service="$service" :index="$loop->index + 1" :provider="$provider" :wire:key="$service->id.''.$provider->id" />
            @empty
                <tr>
                    <td class="py-3 text-sm text-center dark:text-gray-400 dark:bg-gray-800" colspan="10">
                        @lang('crud.general.not_found')
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>