<?php

namespace App\Http\Livewire\User;

use Exception;
use Livewire\Component;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\User\RequestApiController;

class GetNewData extends Component
{
    public $lat;
    public $lng;
    public $lastNotification = 0;

    protected $listeners = ['updateUserLocation'];

    public function getExistingRequest()
    {
        try {
            $response = (new RequestApiController)->request_status_check();
            $response = json_decode(json_encode($response->getData()), true);

            if($response['data']) {
                // ? Emit To Global JS To Handle The Request.
                $this->emit('receive_request', $response['data'][0]);
            }
            else {
                $this->emit('receive_request', null);
            }
        } catch (Exception $e) {
            Log::info("User Get Existing Request Error: ".$e->getMessage());
        }
    }

    /**
     * ? Updates The user Location
     */
    public function updateUserLocation(Request $request, $lat, $lng)
    {
        $this->lat = $request['latitude'] = $lat;
        $this->lng = $request['longitude'] = $lng;

        (new RequestApiController)->show_providers($request);
    }

    // TODO: Put condition for date.
    public function checkNotification()
    {
        $notificationCount = Notification::where('id', '>', $this->lastNotification)
            ->where('status', '1')
            ->whereIn('notify_type', ['user', 'all'])
            ->latest()
            ->orderBy('id', 'desc')
            ->count();

        if($notificationCount) {
            $notification = Notification::where('id', '>', $this->lastNotification)
                ->where('status', '1')
                ->whereIn('notify_type', ['user', 'all'])
                ->limit(15)
                ->latest()
                ->orderBy('id', 'desc')
                ->limit(1)
                ->first();
            $this->lastNotification = $notification->id;
            $this->emit('newNotification');
        }
    }

    public function render()
    {
        $this->getExistingRequest();
        $this->checkNotification();
        return view('livewire.user.get-new-data');
    }
}
