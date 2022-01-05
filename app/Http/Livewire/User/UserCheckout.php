<?php

namespace App\Http\Livewire\User;

use Exception;
use App\Models\Chat;
use App\Models\User;
use Livewire\Component;
use App\Models\ServiceType;
use App\Models\UserRequest;
use App\Models\CancelReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\User\UserApiController;
use App\Http\Controllers\Api\User\RequestApiController;

class UserCheckout extends Component
{
    public $serviceType = null;
    public $geoFence;
    public $serviceSettings;
    public $availableServices = null;
    public $serviceHaveParent = false;
    public $user;
    public $paymentMethod;
    public $distance;

    // Alpine Variables
    public $showServices;
    public $showFair;

    // User current Location;
    public $s_lat = NULL;
    public $s_lng = NULL;
    public $s_address = NULL;

    // destination location;
    public $d_lat = NULL;
    public $d_lng = NULL;
    public $d_address = NULL;

    // Fair Details
    public $base_price = NULL;
    public $tax_price = NULL;
    public $estimated_fare = NULL;
    public $time = NULL;
    public $coupon = NULL;

    // Active Request.
    public $status = NULL;
    public $request_id = NULL;
    public $havePendingRequest = false;
    public $userRequest = NULL;
    public $cancellationReasons = NULL;
    public $selectedReason = NULL;

    // Rating Provider
    public $rating = NULL;
    public $comment = NULL;

    // Chat
    public $chat = NULL;
    public $last_message_id = NULL;
    public $message = NULL;
    private $msgCount = 10;

    // Others
    public $sem;

    protected $listeners = [
        'changeRequestLocation',
        'update_distance',
        'passRequestToComponent',
    ];

    /**
     * This updates the user location.
     * @param Request $request
     * @param mixed $lat
     * @param mixed $lng
     * 
     * @return [type]
     */
    public function changeRequestLocation($lat, $lng, $address, $type = 's')
    {
        if($type == 's') {
            $this->s_lat = $credentials['latitude'] = $lat;
            $this->s_lng = $credentials['longitude'] = $lng;

            $user = User::where('id', auth()->user()->id)->first();
            $user->update($credentials);

            $this->showServices = false;
            $this->showFair = false;
            $this->s_address = $address;
        }
        else if($type == 'd') {
            $this->d_lat = $lat;
            $this->d_lng = $lng;
            $this->d_address = $address;
        }
        $this->showFair = false;
    }

    public function update_distance($total)
    {
        $this->distance = $total;
    }

    /**
     * Get Rate Values
     * ! Removed because The RequestApiController does not contain estimated_fare method. Also for now the required prices are already available at frontend.
     * ? If required check other method called fare of RequestApiController.
     * ? Code Available at cab version.
     * 
     * @param Request $request
     * 
     * @return [type]
     */
    /* public function updateRates(Request $request)
    {
    } */

    /**
     * Fills data to the availableServices and showServices public properties.
     * @param Request $request
     * 
     * @return [type]
     */
    public function loadServices(Request $request)
    {
        $settings = json_decode(json_encode((new UserApiController)->settings($request)->getData()));
        $this->availableServices = collect($settings->serviceTypes);

        // $ids = [];

        // foreach($services as $service) {
        //     array_push($ids, $service->id);
        // }
        // $this->availableServices = ServiceType::whereIn('id', $ids)->get();

        // $this->serviceSettings = collect($settings->referral);

        // // Do this only if there are available services.
        // if($this->availableServices->count()) {
        //     $this->showServices = true;
        //     $this->serviceType = $this->availableServices->first()->id;
        // }
        // else {
        //     $this->emit('livewire_error', 'No Services Found In This Location');
        // }
    }

    /**
     * Sends The Request To Available Providers.
     * 
     * @param 
     * @return [type]
     */
    public function sendRequest(Request $request)
    {
        $request['payment_mode'] = 'CASH';
        $request['distance'] = $this->distance;
        $request['d_latitude'] = $this->s_lat;
        $request['d_longitude'] = $this->s_lng;
        $request['d_address'] = $this->s_address;
        $request['s_latitude'] = $this->s_lat;
        $request['s_longitude'] = $this->s_lng;
        $request['s_address'] = $this->s_address;
        $request['promocode_id'] = 0;
        $request['rental_hours'] = 0;
        $request['service_type'] = $this->serviceType;
        $request['service_required'] = "none";
        try {
            $response = (new RequestApiController)->send_request($request);
            
            // TODO: Make Sure the above function always throws error instead of sending errors in a JSON response & remove this condition below. Errors will be handled using Exceptions.
            if(!empty($response->error)) {
                Log::error($response->error);
                throw new Exception($response->error);
            }

            if(!empty($response)) {
                $response = json_decode($response, true);
                $this->status = 'SEARCHING';
                $this->emit('livewire_success', $response['message']);
            }
        } catch(Exception $e) {
            $this->emit('livewire_error', $e->getMessage());
        }

    }


    public function passRequestToComponent(Request $request, $req)
    {
        if($req && $this->request_id == NULL) {
            // TODO: Plot The points on map.
            $this->fillRequestVariables($request, $req);
            $this->userRequest = UserRequest::findOrFail($this->request_id);
        }
        else if($req && $this->status != $req['status'] && $this->status != 'CANCEL') {
            // ! Request Status Changed. Check this in the flow.
            $this->status = $req['status'];
        }
        else if(empty($req) && $this->status == 'SEARCHING') {
            $this->emit('livewire_error', trans('crud.response.no_provider_available'));
            $this->emptyRequestVariables();
        }
    }

    public function fillRequestVariables(Request $request, $req)
    {
        // TODO: Someway to sanitize these variables.

        // Alpine Variables
        $this->showFair = true;

        // Request Variables
        $this->request_id = $req['id'];
        $this->status = $req['status'];
        $this->s_address = $req['s_address'];
        $this->s_lat = $req['s_latitude'];
        $this->s_lng = $req['s_longitude'];
        $this->d_address = $req['d_address'];
        $this->d_lat = $req['d_latitude'];
        $this->d_lng = $req['d_longitude'];
        $this->serviceType = $req['service_type_id'];
        $this->distance = $req['distance'];

        // $this->updateRates($request);
    }

    public function emptyRequestVariables()
    {
        $this->showFair = false;

        // Request Variables
        $this->request_id = null;
        $this->status = null;
        $this->s_address = null;
        $this->s_lat = null;
        $this->s_lng = null;
        $this->d_address = null;
        $this->d_lat = null;
        $this->d_lng = null;
        $this->serviceType = null;
        $this->distance = null;
        
        $this->base_price = NULL;
        $this->tax_price = NULL;
        $this->estimated_fare = NULL;
        $this->time = NULL;
        $this->coupon = NULL;

        $this->havePendingRequest = false;
        $this->userRequest = NULL;
    }

    public function saveRating(Request $request)
    {
        $request['rating'] = $this->rating;
        $request['comment'] = $this->comment;
        $request['request_id'] = $this->request_id;

        try {
            (new RequestApiController)->rate_provider($request);
            $this->emptyRequestVariables();
            $this->emit('livewire_success', trans('crud.response.request_completed'));
        } catch(Exception $e) {
            Log::error("Provider Could Not Rate The Request.");
            $this->emit('livewire_error', $e->getMessage());
        }
    }

    public function sendMessage()
    {
        
        if(!empty($this->message)) {
            $credentials['request_id'] = $this->userRequest->id;
            $credentials['user_id'] = $this->userRequest->user->id;
            $credentials['provider_id'] = $this->userRequest->provider->id;
            $credentials['message'] = $this->message;
            $credentials['type'] = 'up';
            $credentials['delivered'] = false;
            
            $this->emit('newMsg');
            $chat = Chat::create($credentials);
            $this->message = NULL;
        }
    }

    public function checkMessages()
    {
        // TODO: Use last_fetched_message
        $newMsgs = Chat::where('request_id', $this->userRequest->id)
            ->limit($this->msgCount)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $newMsgs = $newMsgs->diff($this->chat)->all();

        if($newMsgs) {
            $this->emit('newMsg');
            foreach($newMsgs as $m) {
                $this->chat->push($m);
                
            }
        }

        DB::table('chats')
            ->where('type', 'up')
            ->where('request_id', $this->request_id)
            ->update(['delivered' => true]);
    }

    public function getCancellationReasons()
    {
        // TODO: If There is No Cancellation Reason Try To Cancel Request Directly.
        if(!$this->cancellationReasons)
            $this->cancellationReasons = CancelReason::where('for', 'user')->get();
        $this->status = 'CANCEL';
    }

    public function exitRequestCancel()
    {
        $this->status = $this->userRequest->status;
    }

    public function cancelRequest(Request $request)
    {
        if($this->selectedReason == NULL) {
            $this->emit('livewire_error', 'Cancel Reason Not Selected');
        }
        else {
            $request['request_id'] = $this->request_id;
            $request['cancel_reason'] = CancelReason::select("reason")->where('id', $this->selectedReason)->first();
            try {
                (new RequestApiController)->cancel_request($request);
                $this->selectedReason = null;
                $this->havePendingRequest = false;
                $this->userRequest = NULL;
                $this->status = '';
                $this->emit('livewire_success', 'Request Cancelled');
            } catch (Exception $e) {
                $this->status = '';
                Log::error('Could Not Cancel Request From Provider: '. $e->getMessage());
                $this->emit('livewire_error', $e->getMessage());
            }
        }
    }

    /**
     * Mounts Data to The Initial Request
     * @param Request $request
     * @param mixed $geoFence
     * @param mixed $serviceType
     * 
     * @return null
     */
    public function mount(Request $request)
    {
        // !Here is the extra something.
        $this->base_price = 807;
        $this->coupon = null;
        $this->d_address = "Anandpur Marg Shiv Puram Phase -I, Colony, Shiv Puram Phase -I, Kamaluaganja, Himmatpur Malla, Haldwani, Uttarakhand 263139, India";
        $this->d_lat = 59.536346;
        $this->d_lng = 64.237398;
        $this->distance = 6.066;
        $this->estimated_fare = 847.35;
        $this->paymentMethod = null;
        $this->s_address = "Anandpur Marg Shiv Puram Phase -I, Colony, Shiv Puram Phase -I, Kamaluaganja, Himmatpur Malla, Haldwani, Uttarakhand 263139, India";
        $this->s_lat = 59.536346;
        $this->s_lng = 64.237398;
        $this->serviceType = 3;
        $this->showFair = true;
        $this->showServices = true;
        $this->tax_price = 40.35;
        $this->time = "16 mins";
        // ! End of extra something

        $response = (new RequestApiController)->request_status_check();
        $response = json_decode(json_encode($response->getData()), true);

        if(!empty($response['data'])) {
            $this->havePendingRequest = true;
            $this->userRequest = UserRequest::findOrFail($response['data'][0]['id']);
            
            $this->chat = Chat::where('request_id', $this->userRequest->id)
                ->limit($this->msgCount)
                ->orderBy('created_at', 'asc')
                ->get();
                
            $this->emitSelf('passRequestToComponent', $response['data'][0]);
            
            // $source = $this->userRequest->s_latitude.' '.$this->userRequest->s_longitude;
            // $destination = $this->userRequest->d_latitude.' '.$this->userRequest->d_longitude;
            // $this->emit('plot_request_coordinates', $source, $destination);
        }
        
        !empty($this->serviceType) ? $this->loadServices($request) : $this->showServices = false;

        $this->sem['startedEvent'] = true;
        $this->sem['pickedUpEvent'] = true;
        $this->sem['resetSem'] = true;
    }
    
    public function render()
    {
        $user = auth()->user('web');

        // ? Setting The Locale For Subsequent Requests.
        $lang = $user->language ?: config('app.fallback_locale');
        app()->setLocale($lang);

        if($this->userRequest) {
            $this->checkMessages();

            if($this->userRequest->status == 'STARTED' && $this->sem['startedEvent']) {
                // ? Emitting the event to plot the provider position and pickup position.
                $origin = (string)$this->userRequest->provider->latitude . "," . (string)$this->userRequest->provider->longitude;
                $destination = (string)$this->userRequest->s_latitude . "," . (string)$this->userRequest->s_longitude;
                $this->emit('plot_request_coordinates', $origin, $destination);
                $this->sem['resetSem'] = true;
                $this->sem['startedEvent'] = false;
            }
            else if($this->userRequest->status == 'PICKEDUP' && $this->sem['pickedUpEvent']) {
                // ? Emitting the event to plot the source and destination of the request.
                $origin = (string)$this->userRequest->s_latitude . "," . (string)$this->userRequest->s_longitude;
                $destination = (string)$this->userRequest->d_latitude . "," . (string)$this->userRequest->d_longitude;
                $this->emit('plot_request_coordinates', $origin, $destination);
                $this->sem['pickedUpEvent'] = false;
    
                $this->sem['mapsRequired'] = false;
            }
            else if($this->userRequest->status == 'COMPLETED' && $this->sem['resetSem']) {
                $this->sem['resetSem'] = false;
                $this->sem['startedEvent'] = true;
                $this->sem['PickedUpEvent'] = true;
            }
    
        }

        return view('livewire.user.user-checkout');
    }
}