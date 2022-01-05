<?php

namespace App\Http\Livewire\Provider;

use Livewire\Component;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\Provider\TripController;

class GetNewData extends Component
{
    public $showingRequest;
    public $lat;
    public $lng;
    public $lastNotification = 0;

    protected $listeners = ['updateProviderLocation'];

    function getNewRequests(Request $request) {
        $request['latitude'] = $this->lat;
        $request['longitude'] = $this->lng;
        $response = (new TripController)->index($request);

        if(count($response['requests'])) {
            // ? Emit To Global JS To Start The Timer.
            $this->emit('receive_request', $response['requests']);
            // $this->emit('passRequestToNotification', true, $response['requests'][0]);
        }
    }

    // TODO: Put condition for date.
    public function checkNotification()
    {
        $notificationCount = Notification::where('id', '>', $this->lastNotification)
            ->where('status', '1')
            ->whereIn('notify_type', ['provider', 'all'])
            ->latest()
            ->orderBy('id', 'desc')
            ->count();

        if($notificationCount) {
            $notification = Notification::where('id', '>', $this->lastNotification)
                ->where('status', '1')
                ->whereIn('notify_type', ['provider', 'all'])
                ->limit(15)
                ->latest()
                ->orderBy('id', 'desc')
                ->limit(1)
                ->first();
            $this->lastNotification = $notification->id;
            $this->emit('newNotification');
        }
    }

    /**
    // ? Updates Provider Location.
     * @param Request $request
     * @param float $lat
     * @param float $lng
     * 
     * @return [type]
     */
    public function updateProviderLocation(Request $request, $lat, $lng)
    {
        $this->lat = $request['latitude'] = $lat;
        $this->lng = $request['longitude'] = $lng;

        (new TripController)->index($request);
    }

    public function render(Request $request)
    {
        $this->getNewRequests($request);
        $this->checkNotification();
        return view('livewire.provider.get-new-data');
    }
}
