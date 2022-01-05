<?php

namespace App\Http\Controllers\Api\User;

// use DB;
// use Log;
// use Route;
// use Notification;
// use App\Models\Time;
// use App\Models\Admin;
// use App\Notifications\WebPush;

use Auth;
use DateTime;
use Exception;
use Carbon\Carbon;
use App\Models\Card;
use App\Models\User;
use GuzzleHttp\Client;
use App\Models\PeakHour;
use App\Models\Provider;
use App\Models\GeoFencing;
use App\Models\PaymentLog;
use App\Models\ServiceType;
use App\Models\UserRequest;
use Illuminate\Http\Request;
use App\Models\RequestFilter;
use App\Services\ServiceTypes;
use App\Models\ProviderService;
use App\Models\ServicePeakHour;
use App\Models\UserRequestRating;
use App\Models\UserRequestDispute;
use App\Models\UserRequestPayment;
use Illuminate\Support\Collection;
use App\Models\UserRequestLostItem;
use App\Http\Controllers\Controller;
use App\Models\CancelReason as Reason;
use App\Models\ServiceRentalHourPackage;
use App\Http\Controllers\SendPushNotification;
use App\Http\Controllers\Api\Provider\TripController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class RequestApiController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function trip_details(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id',
        ]);

        try {
            $UserRequests = UserRequest::UserTripDetails(Auth::user()->id, $request->request_id)->get();

            if (!empty($UserRequests)) {
                $map_icon = asset('asset/img/marker-start.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = 'https://maps.googleapis.com/maps/api/staticmap?' .
                        'autoscale=1' .
                        '&size=320x130' .
                        '&maptype=terrian' .
                        '&format=png' .
                        '&visual_refresh=true' .
                        '&markers=icon:' . $map_icon . '%7C' . $value->s_latitude . ',' . $value->s_longitude .
                        '&markers=icon:' . $map_icon . '%7C' . $value->d_latitude . ',' . $value->d_longitude .
                        '&path=color:0x191919|weight:3|enc:' . $value->route_key .
                        '&key=' . config('constants.map_key');
                }

                $UserRequests[0]->dispute = UserRequestDispute::where('dispute_type', 'user')->where('request_id', $request->request_id)->where('user_id', Auth::user()->id)->first();

                $UserRequests[0]->lostitem = UserRequestLostItem::where('request_id', $request->request_id)->where('user_id', Auth::user()->id)->first();

                $UserRequests[0]->contact_number = config('constants.contact_number', '');
                $UserRequests[0]->contact_email  = config('constants.contact_email', '');
            }
            return $UserRequests;
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return Collection
     */
    public function services(Request $request)
    {
        if ($serviceList = ServiceType::with('rental_hour_package')->get()) {
            return $serviceList;
        } else {
            return response()->json(['error' => trans('api.services_not_found')], 500);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function send_request(Request $request)
    {
        if ($request->ajax()) {
            $this->validate($request, [
                's_latitude'   => 'required|numeric',
                's_longitude'  => 'required|numeric',
                'd_latitude'   => 'numeric',
                'd_longitude'  => 'numeric',
                'service_type' => 'required|numeric|exists:service_types,id',
                //'promo_code' => 'exists:promocodes,promo_code',
                //'distance' => 'required|numeric',
                'use_wallet' => 'numeric',

                //TODO ALLAN - Alterações Debit na máquina e voucher
                'payment_mode' => 'required|in:BRAINTREE,CASH,DEBIT_MACHINE,CARD,PAYPAL,PAYPAL-ADAPTIVE,PAYUMONEY,PAYTM',

                'card_id' => ['required_if:payment_mode,CARD', 'exists:cards,card_id,user_id,' . Auth::user()->id],
            ]);
        } else {
            $this->validate($request, [
                's_latitude'   => 'required|numeric',
                's_longitude'  => 'required|numeric',
                'd_latitude'   => 'numeric',
                'd_longitude'  => 'numeric',
                'service_type' => 'required|numeric|exists:service_types,id',
                //'promo_code' => 'exists:promocodes,promo_code',
                //'distance' => 'required|numeric',
                'use_wallet' => 'numeric',

                'payment_mode' => 'required|in:BRAINTREE,CASH,CARD,DEBIT_MACHINE,PAYPAL,PAYPAL-ADAPTIVE,PAYUMONEY,PAYTM',

                'card_id' => ['required_if:payment_mode,CARD', 'exists:cards,card_id,user_id,' . Auth::user()->id],
            ]);
        }

        $check =$this->poly_check_request((round($request->s_latitude, 6)), (round($request->s_longitude, 6)));

        if ($check == 'no') {
            if ($request->ajax()) {
                return response()->json(['message' => 'Service is not available at this location.'], 422);
            } else {
                return redirect('dashboard')->with('flash_error', 'Service is not available at this location.');
            }
        }
        //\Log::alert($request->all());
        $geo_check = $this->poly_check_new((round($request->s_latitude, 6)), (round($request->s_longitude, 6)));
        //\Log::alert($geo_check);

        $ActiveRequests = UserRequest::PendingRequest(Auth::user()->id)->count();

        if ($ActiveRequests > 0) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.ride.request_inprogress')], 422);
            } else {
                return redirect('dashboard')->with('flash_error', trans('api.ride.request_inprogress'));
            }
        }

        if ($request->has('schedule_date') && $request->has('schedule_time')) {
            $beforeschedule_time = (new Carbon("$request->schedule_date $request->schedule_time"))->subHour(1);
            $afterschedule_time  = (new Carbon("$request->schedule_date $request->schedule_time"))->addHour(1);

            $CheckScheduling = UserRequest::where('status', 'SCHEDULED')
                ->where('user_id', Auth::user()->id)
                ->whereBetween('schedule_at', [$beforeschedule_time, $afterschedule_time])
                ->count();

            if ($CheckScheduling > 0) {
                if ($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.request_scheduled')], 422);
                } else {
                    return redirect('dashboard')->with('flash_error', trans('api.ride.request_scheduled'));
                }
            }
        }

        $distance = config('constants.provider_search_radius', '10');

        $latitude     = $request->s_latitude;
        $longitude    = $request->s_longitude;
        $service_type = $request->service_type;

        $Providers = Provider::with('service')
        //->get();
        ->select(DB::Raw("round((6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ),3) AS distance"), 'id')
        ->where('status', 'approved')
        ->whereRaw("round((6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ),3) <= $distance")
        ->whereHas('service', function ($query) use ($service_type) {
            $query->where('status', 'active');
            $query->where('service_type_id', $service_type);
        })
        ->orderBy('distance', 'asc')
        ->get();
        // dd($Providers);
        //Log::info($Providers);
        // List Providers who are currently busy and add them to the filter list.

        if (count($Providers) == 0) {
            if ($request->ajax()) {
                // Push Notification to User
                return response()->json(['error' => trans('api.ride.no_providers_found')], 422);
            } else {
                return back()->with('flash_success', trans('api.ride.no_providers_found'));
            }
        }

        try {
            $details = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . $request->s_latitude . ',' . $request->s_longitude . '&destination=' . $request->d_latitude . ',' . $request->d_longitude . '&mode=driving&key=' . config('constants.map_key');

            $json = curl($details);

            $details = json_decode($json, true);

            $route_key = (count($details['routes']) > 0) ? $details['routes'][0]['overview_polyline']['points'] : '';

            $UserRequest             = new UserRequest();
            $UserRequest->booking_id = generate_booking_id();
            if ($request->has('braintree_nonce') && $request->braintree_nonce != null) {
                $UserRequest->braintree_nonce = $request->braintree_nonce;
            }
            if ($geo_check != 0) {
                $UserRequest->geo_fencing_id = $geo_check;
            }
            $UserRequest->user_id = Auth::user()->id;

            if ((config('constants.manual_request', 0) == 0) && (config('constants.broadcast_request', 0) == 0)) {
                $UserRequest->current_provider_id = $Providers[0]->id;
            } else {
                $UserRequest->current_provider_id = 0;
            }

            try {
                $response = new ServiceTypes();

                //$responsedata = $response->calculateFare($request->all(), 1);

                //$UserRequest->estimated_fare = $responsedata['data']['estimated_fare'];
                $UserRequest->estimated_fare = 0;//$responsedata['data']['estimated_fare'];
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            $UserRequest->service_type_id = $request->service_type;
            //$UserRequest->rental_hours = $request->rental_hours;
            $UserRequest->payment_mode = $request->payment_mode;
            $UserRequest->promocode_id = $request->promocode_id ?: 0;

            $UserRequest->status = 'SEARCHING';

            $UserRequest->s_address = $request->s_address ?: '';
            $UserRequest->d_address = $request->d_address ?: '';

            $UserRequest->s_latitude  = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;

            $UserRequest->d_latitude  = $request->d_latitude ? $request->d_latitude : $request->s_latitude;
            $UserRequest->d_longitude = $request->d_longitude ? $request->d_longitude : $request->s_longitude;

            if ($request->d_latitude == null && $request->d_longitude == null) {
                $UserRequest->is_drop_location = 0;
            }

            $UserRequest->destination_log = json_encode([['latitude' => $UserRequest->d_latitude, 'longitude' => $request->d_longitude, 'address' => $request->d_address]]);
            $UserRequest->distance        = 0; //$request->distance;
            $UserRequest->unit            = config('constants.distance', 'Kms');

            if (Auth::user()->wallet_balance > 0) {
                $UserRequest->use_wallet = $request->use_wallet ?: 0;
            }

            if (config('constants.track_distance', 0) == 1) {
                $UserRequest->is_track = 'YES';
            }

            $UserRequest->otp = mt_rand(1000, 9999);

            $UserRequest->assigned_at = Carbon::now();
            $UserRequest->route_key   = $route_key;

            if ($Providers->count() <= config('constants.surge_trigger') && $Providers->count() > 0) {
                $UserRequest->surge = 1;
            }

            if ($request->has('schedule_date') && $request->has('schedule_time')) {
                $UserRequest->status       = 'SCHEDULED';
                $UserRequest->schedule_at  = date('Y-m-d H:i:s', strtotime("$request->schedule_date $request->schedule_time"));
                $UserRequest->is_scheduled = 'YES';
            }

            if ($UserRequest->status != 'SCHEDULED') {
                if ((config('constants.manual_request', 0) == 0) && (config('constants.broadcast_request', 0) == 0)) {
                    //Log::info('New Request id : ' . $UserRequest->id . ' Assigned to provider : ' . $UserRequest->current_provider_id);
                    //(new SendPushNotification())->IncomingRequest($Providers[0]->id);
                }
            }

            $UserRequest->save();

            // if ((config('constants.manual_request', 0) == 0)) {
            //     $admins = Admin::select('id')->get();

            //     foreach ($admins as $admin_id) {
            //         $admin = Admin::find($admin_id->id);
            //         $admin->notify(new WebPush('Notifications', trans('api.push.incoming_request'), route('admin.dispatcher.index')));
            //     }
            // }

            // update payment mode
            User::where('id', Auth::user()->id)->update(['payment_mode' => $request->payment_mode]);

            if ($request->has('card_id')) {
                Card::where('user_id', Auth::user()->id)->update(['is_default' => 0]);
                Card::where('card_id', $request->card_id)->update(['is_default' => 1]);
            }

            if ($UserRequest->status != 'SCHEDULED') {
                if (config('constants.manual_request', 0) == 0) {
                    foreach ($Providers as $key => $Provider) {
                        if (config('constants.broadcast_request', 0) == 1) {
                            (new SendPushNotification())->IncomingRequest($Provider->id);
                        }

                        $Filter = new RequestFilter();
                        // Send push notifications to the first provider
                        // incoming request push to provider

                        $Filter->request_id  = $UserRequest->id;
                        $Filter->provider_id = $Provider->id;
                        $Filter->save();
                    }
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'message'          => ($UserRequest->status == 'SCHEDULED') ? 'Request schedule created!' : 'New Request created!',
                    'request_id'       => $UserRequest->id,
                    'current_provider' => $UserRequest->current_provider_id,
                ]);
            } else {
                if ($UserRequest->status == 'SCHEDULED') {
                    $request->session()->flash('flash_success', 'Your Request is scheduled!');
                }
                return redirect('dashboard');
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong') . $e->getMessage()], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong') . $e->getMessage());
            }
        }
    }

    /**
     * Show the nearby providers.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_providers(Request $request)
    {
        $this->validate($request, [
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'service'   => 'numeric|exists:service_types,id',
        ]);

        // TODO - Update user city by latitude and longitude

        User::where('id', Auth::user()->id)->update(['latitude' => $request->latitude, 'longitude' => $request->longitude]);

        try {
            //Alterado por Allan
            $distance  = config('constants.provider_search_radius', '10');
            $latitude  = $request->latitude;
            $longitude = $request->longitude;

            if ($request->has('service')) {
                $ActiveProviders = ProviderService::AvailableServiceProvider($request->service)
                    ->get()->pluck('provider_id');

                $Providers = Provider::with('service')->whereIn('id', $ActiveProviders)
                    ->where('status', 'approved')
                    ->whereRaw("round((6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ),3) <= $distance")
                    ->get();
            } else {
                $ActiveProviders = ProviderService::where('status', 'active')
                    ->get()->pluck('provider_id');

                $Providers = Provider::with('service')->whereIn('id', $ActiveProviders)
                    ->where('status', 'approved')
                    ->whereRaw("round((6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ),3) <= $distance")
                    ->get();
            }

            return $Providers;
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel_request(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|numeric|exists:user_requests,id,user_id,' . Auth::user()->id,
        ]);

        try {
            $UserRequest = UserRequest::findOrFail($request->request_id);

            if ($UserRequest->status == 'CANCELLED') {
                if ($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.already_cancelled')], 422);
                } else {
                    return back()->with('flash_error', trans('api.ride.already_cancelled'));
                }
            }

            if (in_array($UserRequest->status, ['SEARCHING', 'STARTED', 'ARRIVED', 'SCHEDULED'])) {
                if ($UserRequest->status != 'SEARCHING') {
                    $this->validate($request, [
                        'cancel_reason' => 'max:255',
                    ]);
                }

                $UserRequest->status = 'CANCELLED';

                if ($request->cancel_reason == 'ot') {
                    $UserRequest->cancel_reason = $request->cancel_reason_opt;
                } else {
                    $UserRequest->cancel_reason = $request->cancel_reason;
                }

                $UserRequest->cancelled_by = 'USER';
                $UserRequest->save();

                RequestFilter::where('request_id', $UserRequest->id)->delete();

                if ($UserRequest->status != 'SCHEDULED') {
                    if ($UserRequest->provider_id != 0) {
                        ProviderService::where('provider_id', $UserRequest->provider_id)->update(['status' => 'active']);
                    }
                }

                // Send Push Notification to User
                (new SendPushNotification())->UserCancellRide($UserRequest);

                if ($request->ajax()) {
                    return response()->json(['message' => trans('api.ride.ride_cancelled')]);
                } else {
                    return redirect('dashboard')->with('flash_success', trans('api.ride.ride_cancelled'));
                }
            } else {
                if ($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.already_onride')], 422);
                } else {
                    return back()->with('flash_error', trans('api.ride.already_onride'));
                }
            }
        } catch (ModelNotFoundException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    /**
     * Verifica distância entre 2 pontos
     *
     * @return \Illuminate\Http\Response
     */
    public function getLocationDistance($locationarr)
    {
        $fn_response=['data'=>null, 'errors'=>null];

        try {
            $s_latitude  = $locationarr['s_latitude'];
            $s_longitude = $locationarr['s_longitude'];
            $d_latitude  = empty($locationarr['d_latitude']) ? $locationarr['s_latitude'] : $locationarr['d_latitude'];
            $d_longitude = empty($locationarr['d_longitude']) ? $locationarr['s_longitude'] : $locationarr['d_longitude'];
            $apiurl      = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $s_latitude . ',' . $s_longitude . '&destinations=' . $d_latitude . ',' . $d_longitude . '&mode=driving&sensor=false&units=imperial&key=' . config('constants.map_key');
            $client      = new Client();
            $location    = $client->get($apiurl);
            $location    = json_decode($location->getBody(), true);

            if (!empty($location['rows'][0]['elements'][0]['status']) && $location['rows'][0]['elements'][0]['status'] == 'ZERO_RESULTS') {
                throw new Exception('Out of service area', 1);
            }
            $fn_response['meter']  =$location['rows'][0]['elements'][0]['distance']['value'];
            $fn_response['time']   =$location['rows'][0]['elements'][0]['duration']['text'];
            $fn_response['seconds']=$location['rows'][0]['elements'][0]['duration']['value'];
        } catch (Exception $e) {
            $fn_response['errors']=trans('user.maperror');
        }
        return round($fn_response['meter'] / 1000, 1);//RETORNA QUILÔMETROS
    }

    
    /**
     * Verifica distância entre 2 pontos
     **60
     * @return \Illuminate\Http\Response
     */
    public function getLiveDirection(Request $request)
    {
        $tag = number_format((float)$request->s_latitude, 7, '.', '');
        $tag2 = number_format((float)$request->d_latitude, 7, '.', '');
        if($request->has('booking_id')){
            $final=$tag . $tag2 .$request->booking_id ?? 'empty';
        }else{
            $final=$tag . $tag2 . auth()->id();
        }
        \Log::alert($final." ".auth()->id()." pro livedirection hit");
        \Log::alert($request);
        // $test = \Cache::remember(
        //     $final.'Location',
        //     5,
        //     function () use ($request,$tag,$tag2) {
                $fn_response=['data'=>null, 'errors'=>null];
                $location   = null;
                try {
                    $s_latitude  = $request->s_latitude;
                    $s_longitude = $request->s_longitude;
                    $d_latitude  = empty($request->d_latitude) ? $request->s_latitude : $request->d_latitude;
                    $d_longitude = empty($request->d_longitude) ? $request->s_longitude : $request->d_longitude;
                    $apiurl      = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . $s_latitude . ',' . $s_longitude . '&destination=' . $d_latitude . ',' . $d_longitude . '&mode=driving&sensor=false&units=imperial&key='.config('constants.server_map_key');
                    \Log::alert($final." cache pro created ProLocation");
                    $client      = new Client();
                    $location    = $client->post($apiurl);
                    $location    = json_decode($location->getBody(), true);
                    //\Log::alert($location);
                    if (!empty($location['rows'][0]['elements'][0]['status']) && $location['rows'][0]['elements'][0]['status'] == 'ZERO_RESULTS') {
                        \Cache::forget($final.'Location');
                        throw new Exception('Out of service area', 1);
                    }
                    $fn_response['meter']  =$location['rows'][0]['elements'][0]['distance']['value'];
                    $fn_response['time']   =$location['rows'][0]['elements'][0]['duration']['text'];
                    $fn_response['seconds']=$location['rows'][0]['elements'][0]['duration']['value'];
                } catch (Exception $e) {
                    $fn_response['errors']=$e;
                }
                //return round($fn_response['meter'] / 1000, 1);//RETORNA QUILÔMETROS
                return $location;
        //     }
        // );
        // return $test;
    }

    /**
    * Verifica distância entre 2 pontos
    *
    * @return \Illuminate\Http\Response
    */
    public function distanceMatrix(Request $request)
    {
        $tag = number_format((float)$request->s_latitude, 2, '.', '');
        $tag2 = number_format((float)$request->d_latitude, 2, '.', '');
        \Log::alert($tag.$tag2." ".auth()->id()." pro live eta hit");
        // $test = \Cache::remember(
        //     $tag.$tag2.auth()->id().'eta',
        //     5*60,
        //     function () use ($request,$tag,$tag2) {
                $fn_response=['data'=>null, 'errors'=>null];
                $location   = null;
                try {
                    $s_latitude  = $request->s_latitude;
                    $s_longitude = $request->s_longitude;
                    $d_latitude  = empty($request->d_latitude) ? $request->s_latitude : $request->d_latitude;
                    $d_longitude = empty($request->d_longitude) ? $request->s_longitude : $request->d_longitude;
                    $apiurl      = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $s_latitude . ',' . $s_longitude . '&destinations=' . $d_latitude . ',' . $d_longitude . '&mode=driving&sensor=false&units=imperial&key='.config('constants.server_map_key');
                    \Log::alert($tag.$tag2." cache pro created eta");
                    $client      = new Client();
                    $location    = $client->post($apiurl);
                    $location    = json_decode($location->getBody(), true);
                    //\Log::alert($location);
                    if (!empty($location['rows'][0]['elements'][0]['status']) && $location['rows'][0]['elements'][0]['status'] == 'ZERO_RESULTS') {
                        \Cache::forget($tag.$tag2.auth()->id().'eta');
                         throw new Exception('Out of service area', 1);
                    }
                    \Log::alert($location);
                    $fn_response['meter']  =$location['rows'][0]['elements'][0]['distance']['value'];
                    $fn_response['time']   =$location['rows'][0]['elements'][0]['duration']['text'];
                    $fn_response['seconds']=$location['rows'][0]['elements'][0]['duration']['value'];
                    $fn_response['distance']=$location['rows'][0]['elements'][0]['distance']['text'];
                    $fn_response['status'] ='OK';
                } catch (Exception $e) {
                    $fn_response['meter']  =0;
                    $fn_response['time']   ='0 mins';
                    $fn_response['seconds']=0;
                    $fn_response['distance']='0 Miles';
                    $fn_response['status'] ='FAIL';
                }
                //return round($fn_response['meter'] / 1000, 1);//RETORNA QUILÔMETROS

                return $fn_response;
        //     }
        // );
        // return $test;
    }

    /**
     * Show the request status check.
     *
     * @return \Illuminate\Http\Response
     */
    public function request_status_check()
    {
        try {
            $check_status = ['CANCELLED', 'SCHEDULED'];

            $UserRequests = UserRequest::UserRequestStatusCheck(Auth::user()->id, $check_status)
            ->latest()
                ->get()
                ->toArray();

            $search_status      = ['SEARCHING', 'SCHEDULED'];
            $UserRequestsFilter = UserRequest::UserRequestAssignProvider(Auth::user()->id, $search_status)->get();

            $package_time_all = ServiceRentalHourPackage::where('service_type_id', 2)->orderBy('hour')->get();

            if (!empty($UserRequests)) {
                //$UserRequests[0]['rent_plan'] = ServiceRentalHourPackage::where('id', $UserRequests[0][rental_hours])->first();
                $UserRequests[0]['ride_otp']  = (int)config('constants.ride_otp', 0);
                $UserRequests[0]['ride_toll'] = (int)config('constants.ride_toll', 0);
                $UserRequests[0]['reasons']   = Reason::where('for', 'user')->get();
            }

            $Timeout = config('constants.provider_select_timeout', 180);
            $type    = config('constants.broadcast_request', 0);

            if (!empty($UserRequestsFilter)) {
                for ($i = 0; $i < sizeof($UserRequestsFilter); $i++) {
                    if ($type == 1) {
                        $ExpiredTime = $Timeout - (time() - strtotime($UserRequestsFilter[$i]->created_at));
                        if ($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime < 0) {
                            UserRequest::where('id', $UserRequestsFilter[$i]->id)->update(['status' => 'CANCELLED']);
                            // No longer need request specific rows from RequestMeta
                            RequestFilter::where('request_id', $UserRequestsFilter[$i]->id)->delete();
                        } elseif ($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime > 0) {
                            break;
                        }
                    } else {
                        $ExpiredTime = $Timeout - (time() - strtotime($UserRequestsFilter[$i]->assigned_at));
                        if ($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime < 0) {
                            $Providertrip = new TripController();
                            $Providertrip->assign_next_provider($UserRequestsFilter[$i]->id);
                        } elseif ($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime > 0) {
                            break;
                        }
                    }
                }
            }

            if (empty($UserRequests)) {
                $cancelled_request = UserRequest::where('user_requests.user_id', Auth::user()->id)
                    ->where('user_requests.user_rated', 0)
                    ->where('user_requests.status', ['CANCELLED'])->orderby('updated_at', 'desc')
                    ->where('updated_at', '>=', \Carbon\Carbon::now()->subSeconds(5))
                    ->first();

                if ($cancelled_request != null) {
                    \Session::flash('flash_error', $cancelled_request->cancel_reason);
                }
            }

            return response()->json([
                'data' => $UserRequests,
                'sos'  => config('constants.sos_number', '190'),
                'cash' => (int) 1, //config('constants.cash'),

                //TODO ALLAN - Alterações Debit na máquina e voucher
                'debit_machine' => (int)config('constants.debit_machine'),
                'voucher'       => (int)config('constants.voucher'),
                'online' => (int)config('constants.online_payment'),
                'card'                   => (int)config('constants.stripe_payment'),
                'currency'               => config('constants.currency', '$'),
                'payumoney'              => (int)config('constants.payumoney'),
                'paypal'                 => (int)config('constants.paypal'),
                'paypal_adaptive'        => (int)config('constants.paypal_adaptive'),
                'braintree'              => (int)config('constants.braintree'),
                'paytm'                  => (int)config('constants.paytm'),
                'stripe_secret_key'      => config('constants.stripe_secret_key'),
                'stripe_publishable_key' => config('constants.stripe_publishable_key'),
                'stripe_currency'        => config('constants.stripe_currency'),
                'payumoney_environment'  => config('constants.payumoney_environment'),
                'payumoney_key'          => config('constants.payumoney_key'),
                'payumoney_salt'         => config('constants.payumoney_salt'),
                'payumoney_auth'         => config('constants.payumoney_auth'),
                'paypal_environment'     => config('constants.paypal_environment'),
                'paypal_currency'        => config('constants.paypal_currency'),
                'paypal_client_id'       => config('constants.paypal_client_id'),
                'paypal_client_secret'   => config('constants.paypal_client_secret'),
                'braintree_environment'  => config('constants.braintree_environment'),
                'braintree_merchant_id'  => config('constants.braintree_merchant_id'),
                'braintree_public_key'   => config('constants.braintree_public_key'),
                'braintree_private_key'  => config('constants.braintree_private_key'),
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong') . $e], 500);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function rate_provider(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id,user_id,' . Auth::user()->id,
            'rating'     => 'required|integer|in:1,2,3,4,5',
            'comment'    => 'max:255',
        ]);

        $UserRequests = UserRequest::where('id', $request->request_id)
            ->where('status', 'COMPLETED')
            ->where('paid', 0)
            ->first();

        if ($UserRequests) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.user.not_paid')], 422);
            } else {
                return back()->with('flash_error', trans('api.user.not_paid'));
            }
        }

        try {
            $UserRequest = UserRequest::findOrFail($request->request_id);

            if ($UserRequest->rating == null) {
                UserRequestRating::create([
                    'provider_id'  => $UserRequest->provider_id,
                    'user_id'      => $UserRequest->user_id,
                    'request_id'   => $UserRequest->id,
                    'user_rating'  => $request->rating,
                    'user_comment' => $request->comment,
                ]);
            } else {
                $UserRequest->rating->update([
                    'user_rating'  => $request->rating,
                    'user_comment' => $request->comment,
                ]);
            }

            $UserRequest->user_rated = 1;
            $UserRequest->save();

            // Send Push Notification to Provider
            if ($request->ajax()) {
                return response()->json(['message' => trans('api.ride.provider_rated')]);
            } else {
                return redirect('dashboard')->with('flash_success', trans('api.ride.provider_rated'));
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function modifiy_request(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id,user_id,' . Auth::user()->id,
            'latitude'   => 'sometimes|nullable|numeric',
            'longitude'  => 'sometimes|nullable|numeric',
            'address'    => 'sometimes|nullable',

            //TODO ALLAN - Alterações débito na máquina e voucher
            'payment_mode' => 'sometimes|nullable|in:BRAINTREE,CASH,CARD,DEBIT_MACHINE,PAYPAL,PAYPAL-ADAPTIVE,PAYUMONEY,PAYTM',

            'card_id' => ['required_if:payment_mode,CARD', 'exists:cards,card_id,user_id,' . Auth::user()->id],
        ]);

        try {
            $UserRequest = UserRequest::findOrFail($request->request_id);

            if (!empty($request->latitude) && !empty($request->longitude)) {
                $UserRequest->d_latitude  = $request->latitude ?: $UserRequest->d_latitude;
                $UserRequest->d_longitude = $request->longitude ?: $UserRequest->d_longitude;
                $UserRequest->d_address   = $request->address ?: $UserRequest->d_address;
            }

            if ($request->has('braintree_nonce') && $request->braintree_nonce != null) {
                $UserRequest->braintree_nonce = $request->braintree_nonce;
            }

            if (!empty($request->payment_mode)) {
                $UserRequest->payment_mode = $request->payment_mode;
                if ($request->payment_mode == 'CARD' && $UserRequest->status == 'DROPPED') {
                    $UserRequest->status = 'COMPLETED';
                }
            }

            $UserRequest->save();

            if ($request->has('card_id')) {
                Card::where('user_id', Auth::user()->id)->update(['is_default' => 0]);
                Card::where('card_id', $request->card_id)->update(['is_default' => 1]);
            }

            // Send Push Notification to Provider
            if ($request->ajax()) {
                return response()->json(['message' => trans('api.ride.request_modify_location')]);
            } else {
                return redirect('dashboard')->with('flash_success', trans('api.ride.request_modify_location'));
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }

    public function fare(Request $request)
    {
        $this->validate($request, [
            's_latitude'   => 'required|numeric',
            's_longitude'  => 'numeric',
            'd_latitude'   => 'required|numeric',
            'd_longitude'  => 'numeric',
            'service_type' => 'required|numeric|exists:service_types,id',
        ]);

        try {
            $response     = new ServiceTypes();
            $responsedata = $response->calculateFare($request->all());

            if (!empty($responsedata['errors'])) {
                throw new Exception($responsedata['errors']);
            } else {
                return response()->json($responsedata['data']);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function chatPush(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|numeric',
            'message' => 'required',
        ]);

        try {
            $user_id = $request->user_id;
            $message = $request->message;
            $sender  = $request->sender;

            (new SendPushNotification())->sendPushToProviderChat($user_id, $message);

            //(new SendPushNotification())->sendPushToUser($user_id, $message);

            return response()->json(['success' => 'true']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function reasons(Request $request)
    {
        $reason = Reason::where('for', 'user')->where('status', 1)->get();

        return $reason;
    }

    public function payment_log(Request $request)
    {
        $log           = PaymentLog::where('transaction_code', $request->order)->first();
        $log->response = $request->all();
        $log->save();
        return response()->json(['message' => trans('api.payment_success')]);
    }

    public function payment_online(Request $request)
    {
        $this->validate($request, [
            'request_id'       => 'required|integer|exists:user_requests,id',
            'payment_type'     => 'in:ONLINE',
            'transaction_code' => 'sometimes',
            'tips'             => 'sometimes',
        ]);

        $UserRequest                  = UserRequest::find($request->request_id);
        $RequestPayment               = UserRequestPayment::where('request_id', $request->request_id)->first();
        $UserRequest->payment_mode    = $request->payment_type;
        $RequestPayment->card         = $RequestPayment->payable;
        $RequestPayment->payable      = 0;
        $RequestPayment->tips         = $request->tips ? $request->tips : 0;
        $RequestPayment->provider_pay = $RequestPayment->provider_pay + ($request->tips ? $request->tips : 0);
        $RequestPayment->save();
        $log                   = new PaymentLog();
        $log->user_type        = 'user';
        $log->transaction_code = $request->transaction_code;
        $log->amount           = $RequestPayment->provider_pay;
        $log->transaction_id   = $request->request_id;
        $log->payment_mode     = $request->payment_type;
        $log->user_id          = \Auth::user()->id;
        $log->save();
        $UserRequest->paid   = 1;
        $UserRequest->status = 'COMPLETED';
        $UserRequest->save();

        //for create the transaction
        (new TripController())->callTransaction($request->request_id);
        if ($request->ajax()) {
            return response()->json(['message' => trans('api.paid')]);
        } else {
            return redirect('dashboard')->with('flash_success', trans('api.paid'));
        }
    }

    public function poly_check_new($s_latitude, $s_longitude)
    {
        $range_data = GeoFencing::get();
        //dd($range_data);

        $yes = $no =  [];

        $longitude_x = $s_latitude;

        $latitude_y =  $s_longitude;
        if (count($range_data) != 0) {
            foreach ($range_data as $ranges) {
                $vertices_x = $vertices_y = [];

                $range_values = json_decode($ranges['ranges'], true);
                //dd($range_values);
                if ($range_values != '') {
                    foreach ($range_values as $range) {
                        $vertices_x[] = $range['lat'];

                        $vertices_y[] = $range['lng'];
                    }

                    $points_polygon = count($vertices_x);
                    //dd($points_polygon);
                    if (is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)) {
                        $yes[] = $ranges['id'];
                    } else {
                        $no[] = 0;
                    }
                }
            }
            //dd($yes[0]." ".$no[0]);
            if (count($yes) != 0) {
                return $yes[0];
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function poly_check_request($s_latitude, $s_longitude)
    {
        $range_data = GeoFencing::get();
        //\Log::alert($range_data);

        $yes = $no =   [];

        $longitude_x = $s_latitude;

        $latitude_y =  $s_longitude;

        if (count($range_data) != 0) {
            foreach ($range_data as $ranges) {
                if (!empty($ranges)) {
                    $vertices_x = $vertices_y = [];

                    $range_values = json_decode($ranges['ranges'], true);
                    //\Log::alert($range_values);
                    if (count($range_values) > 0) {
                        foreach ($range_values as $range) {
                            $vertices_x[] = $range['lat'];
                            $vertices_y[] = $range['lng'];
                        }
                    }

                    $points_polygon = count($vertices_x);
                    if (is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)) {
                        $yes[] =$ranges['id'];
                    } else {
                        $no[] = 0;
                    }
                }
            }
        }

        if (count($yes) != 0) {
            return 'yes';
        } else {
            return 'no';
        }
    }
}
