<?php

namespace App\Http\Livewire\Provider;

use Livewire\Component;
use Illuminate\Http\Request;
use App\Models\ProviderService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\Provider\ProfileController;

class GoOffline extends Component
{

    public $status = NULL;

    public function toggleStatus(Request $request)
    {
        if($this->status == 'offline') {
            $this->status = 'active';
            $request['service_status'] = 'active';
            $response = (new ProfileController)->available($request);
        }
        else if($this->status == 'active') {
            $this->status = 'offline';
            $request['service_status'] = 'offline';
            $response = (new ProfileController)->available($request);
        }
    }

    public function mount()
    {
        // 'active','offline','riding','hold','balance'
        $balanceService = ProviderService::where('provider_id', auth('provider')->user()->id)
            ->where('status', 'balance')->count();
        if($balanceService) {
            $this->status = 'balance';
            return;
        }
        
        $ridingService = ProviderService::where('provider_id', auth('provider')->user()->id)
            ->where('status', 'riding')->count();
        if($ridingService) {
            $this->status = 'riding';
            return;
        }

        $activeService = ProviderService::where('provider_id', auth('provider')->user()->id)
            ->where('status', 'active')->count();
        if($this->status == NULL && $activeService) {
            $this->status = 'active';
            return;
        }

        $offlineService = ProviderService::where('provider_id', auth('provider')->user()->id)
            ->where('status', 'offline')->count();
        if($this->status == NULL && $offlineService) {
            $this->status = 'offline';
            return;
        }

    }
    
    public function render()
    {
        return view('livewire.provider.go-offline');
    }
}
