<?php

namespace App\Http\Livewire\Provider;

use Exception;
use App\Models\Chat;
use App\Models\User;
use Livewire\Component;
use App\Models\UserRequest;
use App\Models\CancelReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\Provider\TripController;
use App\Http\Controllers\Api\User\RequestApiController;

class IncomingRequest extends Component
{
    protected $listeners = ['passRequestToNotification' => 'incomingRequest', 'refreshComponent' => '$refresh'];
    public $thereIsRequest;

    // Incoming Request Variables.
    public $s_address;
    public $d_address;
    public $request_id;
    public $estimated_fare;
    public $distance;
    public $user;
    public $booking_id;
    public $status;
    public $req;
    public $cancelReasons = NULL;
    public $selectedReason = NULL;
    public $payment_mode = 'CASH';
    public $payment_amount = 0;
    public $userRequest;
    public $incomingRequest = false;

    public $rating;
    public $comment;

    // Chat
    public $chat = NULL;
    public $last_message_id = NULL;
    public $message = NULL;
    private $msgCount = 10;

    public $renderCount = true;
    public $sem;

    // TODO: Load Older Messages Not Done.

    public function incomingRequest(Bool $thereIsRequest, $req = NULL)
    {
        if($thereIsRequest) {
            if($req) {
                // TODO: Check If you need to Empty These Variables.
                $this->req = $req['request'];
                $this->request_id = $req['request_id'];
                $this->s_address = $req['request']['s_address'];
                $this->d_address = $req['request']['d_address'];
                $this->estimated_fare = $req['request']['estimated_fare'];
                $this->distance = $req['request']['distance'];
                $this->booking_id = $req['request']['booking_id'];
                $this->status = $req['request']['status'];
                $this->payment_mode = $req['request']['payment_mode'];

                // Get Everything from this userRequest now remove above properties.
                $this->userRequest = UserRequest::where('id', $this->request_id)->first();
            }
            // ? If there was no request before this then reinitialize the sem variables values.
            if(!$this->thereIsRequest) {
                $this->sem['initMap'] = true;
                $this->sem['mapsRequired'] = true;
                $this->sem['startedEvent'] = true;
                $this->sem['pickedUpEvent'] = true;
            }
            $this->thereIsRequest = true;
            $this->incomingRequest = true;
        }
        $this->thereIsRequest = $thereIsRequest;
        $this->emitSelf('refreshComponent');
    }

    public function rejectRequest()
    {
        try {
            (new TripController)->destroy($this->request_id);
            // ? Emit Event To Global JS To Stop The Timer.
            $this->emit('stop_timer');
            $this->thereIsRequest = false;
            $this->userRequest = NULL;
        } catch(Exception $e) {
            Log::error("Couldn't cancel request form Provider Panel:- ".$e->getMessage());
            $this->emit('livewire_error', 'Couldn\'t cancel Request right now. Try again Later.');
        }
    } 

    /**
     * Accept The Incoming Request.
     * @param Request $request
     * 
     * @return Null|Response response is the error notification in case of error.
     */
    public function acceptRequest(Request $request)
    {
        try {
            (new TripController)->accept($request, $this->request_id);

            // ? Emit Event To Global JS To Stop The Timer.
            $this->emit('stop_timer');
            $this->emit('start_map');
            $this->status = 'STARTED';

            // ? Update the Map Plot. Now It will show the direction from provider's current location to the user's current location which is where the request will be started.
            $provider = auth('provider')->user();
            
            // ? Event to plot the map is in the render. (Needs to be there)

            $this->userRequest = UserRequest::where('id', $this->userRequest->id)->first();

        } catch(Exception $e) {
            Log::error("Couldn't accept request form Provider Panel:- ".$e->getMessage()); 
            $this->emit('livewire_error', 'Couldn\'t accept Request right now. Try again Later.');
        }
    }

    public function updateRequestStatus(Request $request)
    {
        try {
            if($this->status == 'STARTED') {
                $status = 'ARRIVED';
            }
            else if($this->status == 'ARRIVED') {
                $status = 'PICKEDUP';

                // ? update map plot. Now it will show the direction from source to destination of the user Request.
                $origin = (string)$this->userRequest->s_latitude . ',' . (string)$this->userRequest->s_longitude;
                $destination = (string)$this->userRequest->d_latitude . ',' . (string)$this->userRequest->d_longitude;
                $this->emit('plot_request_coordinates', $origin, $destination);
            }
            else if($this->status == 'PICKEDUP') {
                $status = 'DROPPED';
            }
            else if($this->status == 'DROPPED') {
                // TODO: Set the Payment Mode Here Too.
                $request['payment_mode'] = $this->payment_mode;
                $status = 'COMPLETED';
            }

            $request['latitude'] = $this->req['s_latitude'];
            $request['longitude'] = $this->req['s_longitude'];
            $request['status'] = $status;

            (new TripController)->update($request, $this->req['id']);
            $this->status = $status;

            $this->emit("livewire_success", "Arrived At Location Successfully");
        } catch (Exception $e) {
            Log::error("Couldn't change status to arrived at Location:- ".$e->getMessage());
            $this->emit('livewire_error', "Status Could Not Be Updated. Please Try Again After Sometime.");
        }
    }

    /**
     * ? Get The Cancellation Form for accepted Request.
     * TODO: Check why the current request does not contain the cancel reason it should've had those reasons.
     */
    public function cancelRequestForm()
    {
        $this->status = 'CANCEL';
        if(!$this->cancelReasons) {
            $this->cancelReasons = CancelReason::where('for', 'provider')->where('status', 1)->get();
        }
    }

    /**
     * ? Submit the Cancel Request Form.
     */
    public function cancelRequest(Request $request)
    {
        if($this->selectedReason == NULL) {
            $this->emit('livewire_error', 'Cancel Reason Not Selected');
        }
        else {
            $request['request_id'] = $this->request_id;
            $request['cancel_reason'] = $this->selectedReason;
            try {
                (new RequestApiController)->cancel_request($request);
                $this->selectedReason = null;
                $this->thereIsRequest = false;
                $this->userRequest = NULL;
                $this->emit('livewire_success', 'Request Cancelled');
            } catch (Exception $e) {
                Log::error('Could Not Cancel Request From Provider: '. $e->getMessage());
                $this->emit('livewire_error', $e->getMessage());
            }
        }
    }

    /**
     * ? Stop Cancelling The Request.
     */
    public function cancelRequestCancel()
    {
        $this->status = $this->req['status'];
    }

    public function saveRating(Request $request)
    {
        $request['rating'] = $this->rating;
        $request['comment'] = $this->comment;

        try {
            (new TripController)->rate($request, $this->request_id);
            $this->thereIsRequest = false;
            $this->rating = null;
            $this->comment = null;
        } catch(Exception $e) {
            Log::error("Provider Could Not Rate The Request.");
            $this->emit('livewire_error', $e->getMessage());
        }
    }
    
    public function getMessages()
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
            ->where('type', 'pu')
            ->where('request_id', $this->request_id)
            ->update(['delivered' => true]);
    }

    public function sendMessage()
    {
        if(!empty($this->message)) {
            $credentials['request_id'] = $this->userRequest->id;
            $credentials['user_id'] = $this->userRequest->user->id;
            $credentials['provider_id'] = $this->userRequest->provider->id;
            $credentials['message'] = $this->message;
            $credentials['type'] = 'pu';
            $credentials['delivered'] = false;
            
            $this->emit('newMsg');
            Chat::create($credentials);
            $this->message = NULL;
        }
    }

    public function mount()
    {
        $this->renderCount = 1;
        
        $userRequest = UserRequest::
            where(function ($query) {
                $query->where('provider_id', auth('provider')->user()->id)
                ->orWhere('current_provider_id', auth('provider')->user()->id);
            })
            ->whereIn('status', ['STARTED', 'SEARCHING', 'ARRIVED', 'PICKEDUP', 'DROPPED'])
            ->first();

        if($userRequest) {
            $this->thereIsRequest = true;
            $this->status = $userRequest->status;
            $this->userRequest = $userRequest;
            $this->request_id = $userRequest->id;
            
            $this->chat = Chat::where('request_id', $userRequest->id)
                ->limit($this->msgCount)
                ->orderBy('created_at', 'asc')
                ->get();
        }
        else {
            $this->thereIsRequest = false;
        }
        
        $this->sem['initMap'] = true;
        $this->sem['mapsRequired'] = true;
        $this->sem['startedEvent'] = true;
        $this->sem['pickedUpEvent'] = true;
    }

    public function render()
    {
        // TODO: when user request time out check it out.
        if($this->status == 'DROPPED') {
            $user_request = UserRequest::findOrFail($this->request_id);
            $this->payment_amount = $user_request->payment->total;
        }

        if($this->userRequest) {
            $this->getMessages();
        }

        if($this->renderCount >= 2 && $this->sem['initMap']) {
            // ? The events to emit should be emitted after first render. Because during the first render the event listener is not registered (incase of refresh). - guess.
            if(in_array($this->userRequest && $this->userRequest->status, ['STARTED', 'ARRIVED', 'PICKEDUP'])) {
                $this->emit('start_map');
            }
            // $this->renderCount = false;
            $this->sem['initMap'] = false;
        }

        // ? If Map has been initialized and maps are still required.
        if($this->userRequest && !$this->sem['initMap'] && $this->sem['mapsRequired']) {
            if($this->userRequest && $this->userRequest->status == 'STARTED' && $this->sem['startedEvent']) {
                // ? Emitting the event to plot the provider position and pickup position.
                $origin = (string)$this->userRequest->provider->latitude . "," . (string)$this->userRequest->provider->longitude;
                $destination = (string)$this->userRequest->s_latitude . "," . (string)$this->userRequest->s_longitude;
                $this->emit('plot_request_coordinates', $origin, $destination);
                $this->sem['startedEvent'] = false;
            }
            else if($this->userRequest && $this->userRequest->status == 'PICKEDUP' && $this->sem['pickedUpEvent']) {
                // ? Emitting the event to plot the source and destination of the request.
                $origin = (string)$this->userRequest->s_latitude . "," . (string)$this->userRequest->s_longitude;
                $destination = (string)$this->userRequest->d_latitude . "," . (string)$this->userRequest->d_longitude;
                $this->emit('plot_request_coordinates', $origin, $destination);
                $this->emit('start_service_timer');
                $this->sem['pickedUpEvent'] = false;

                $this->chat = NULL;

                $this->sem['mapsRequired'] = false;
            }
        }

        $this->renderCount++;

        return view('livewire.provider.incoming-request');
    }
}