<div class="flex justify-center items-center w-full">
    <span class="dark:text-gray-200 text-gray-800 mr-1">
        Current Status:
    </span>
    @if(in_array($status, ['active', 'offline']))
        <button class="py-3 px-6 bg-blue-500 rounded hover:bg-blue-600 text-white" wire:loading.remove wire:click="toggleStatus">
            {{ ucfirst($this->status) }}
        </button>
        <button class="py-3 px-6 bg-blue-500 rounded hover:bg-blue-600 text-white" wire:loading>
            <i class="fa fa-refresh fa-spin"></i> Changing Status
        </button>
    @endif
    @if($status == 'balance')
        <button class="py-3 px-6 bg-blue-500 rounded hover:bg-blue-600 text-white">
            You Need To Settle Balance With The Admin.
        </button>
    @endif
</div>