<?php

namespace App\Http\Controllers\Provider;

use Carbon\Carbon;
use App\Models\Document;
use App\Models\UserRequest;
use Illuminate\Support\Arr;

use Illuminate\Http\Request;
use App\Models\ProviderWallet;
use App\Models\ProviderDocument;
use App\Models\UserRequestPayment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\StripeController;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\Provider\UpdateProfileRequest;
use App\Http\Requests\Provider\StoreVerificationDocumentsRequest;

class HomeController extends Controller
{
    /**
     * Return the Provider Dashboard
     * 
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        $cancelled_rides = UserRequest::where('status', 'CANCELLED')->count();
        $completed_rides = UserRequest::where('status', 'COMPLETED')->count();
        $today = Carbon::now()->toDate()->format('Y-m-d');
        $revenue_of_month = DB::select(
                "SELECT 
                    sum(total) as total,
                    provider_id,
                    DATE_FORMAT(created_at, '%Y-%m') as duration
                FROM
                    user_request_payments
                GROUP BY duration, provider_id
                HAVING provider_id = 1 AND duration = DATE_FORMAT($today, '%Y-%m')
                ORDER BY duration asc"
            );
        if($revenue_of_month) {
            $month_revenue = $revenue_of_month[0]->total;
    }
        else {
            $month_revenue = 0;
        }

        $total_revenue = UserRequestPayment::where('provider_id', auth()->user('provider')->id)->sum('total');
        // dd($revenue_of_month);
        return view('provider.dashboard', compact('cancelled_rides', 'completed_rides', 'month_revenue', 'total_revenue'));
    }

    /**
     * ? Return the Provider Settings Page.
     * TODO: Try To Get All The Required Documents In A Single Query.
     * 
     * @return Illuminate\Http\Response
     */
    public function settings()
    {
        $given = auth()->user('provider')->documents;
        $ids = $given ? $given->pluck('document_id')->toArray() : [];

        $requiredIds = Document::select('id')->where('status', '1')->get()->pluck('id')->toArray();

        $notGivenIds = array_diff($requiredIds, $ids);
        $notGiven = Document::whereIn('id', $notGivenIds)->get();

        return view('provider.settings', compact('given', 'notGiven'));
    }

    /**
     * Updates the Provider Password
     * 
     * @param ChangePasswordRequest $request
     * @return Illuminate\Http\Response
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $credentials = $request->only('password');
        if (!Hash::check($request->old_password, $request->user()->password)) {
            return redirect(route('provider.settings')."#changePassword")
                ->withErrors([
                    'old_password' => 'Incorrect Old Password',
                ]);
        }

        $credentials['password'] = Hash::make($request['password']);
        $provider = $request->user('provider');
        $provider->update($credentials);

        return redirect(route('provider.settings')."#changePassword")
            ->with('success', __('crud.inputs.password')." ".__('crud.general.updated'));
    }

    /**
     * @return [type]
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $credentials = $request->validated();

        $provider = $request->user('provider');

        if ($request->hasFile('avatar')) {
            if ($provider->avatar) {
                Storage::delete($provider->avatar);
            }

            $credentials['avatar'] = $request->file('avatar')->store('public/provider/avatars');
        }

        $provider->update($credentials);

        return redirect(route('provider.settings')."#general")
            ->with('success', __('crud.navlinks.profile')." ".__('crud.general.updated'));
    }

    public function uploadVerificationDocument(StoreVerificationDocumentsRequest $request)
    {
        $credentials = $request->validated()['document']; // submitted Documents
        
        if(!$credentials) {
            // No Document Submitted
            return redirect(route('provider.settings')."#verification")
                ->withErrors("No Document Found");
        }
        $keys = array_keys($credentials); // These will be the document id's

        // all the required documents
        $required = Arr::flatten(Document::select('id')->where('status', '1')->get()->toArray());
        
        // Submitted By Provider
        $given = Arr::flatten(ProviderDocument::select('document_id')->where('provider_id', auth()->user('provider')->id)->get()->toArray());

        $providerDocument['provider_id'] = auth()->user('provider')->id;
        $providerDocument['status'] = 'ASSESSING';

        // Not Submitted
        $to_submit = array_diff($required, $given);

        foreach($credentials as $key => $value) {
            // Iterate Through Each Document
            if(in_array($key, $to_submit)) {
                $providerDocument['document_id'] = $key;
                $providerDocument['url'] = $value->store('public/provider/document');
                ProviderDocument::create($providerDocument);
            }
        }

        return redirect(route('provider.settings')."#verification")
            ->withSuccess("Document Uploaded Successfully");
    }
    
    public function notification()
    {
        return view('provider.notification');
    }

    // ! REMOVE IF NOT REQUIRED ANYMORE. USED for track.blade.php
    public function track()
    {
        $ride = UserRequest::where('id', 153)->first();
        return view('provider.track', compact('ride'));
    }

    // ! REMOVE IF NOT REQUIRED ANYMORE. USED IN track.blade.php
    public function track_location(Request $request) {

        // ! Removed condition :- ->where('user_requests.status', 'PICKEDUP')
        $ride = UserRequest::select('user_requests.track_latitude AS s_latitude', 'user_requests.track_longitude AS s_longitude', 'user_requests.d_latitude', 'user_requests.d_longitude', 'service_types.image')->leftjoin('service_types', 'service_types.id', '=', 'user_requests.service_type_id')->where('user_requests.id', $request->id)->first();

        if($ride != null) {
            $s_latitude = $ride->s_latitude;
            $s_longitude = $ride->s_longitude;
            $d_latitude = $ride->d_latitude;
            $d_longitude = $ride->d_longitude;

            $apiurl = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$s_latitude.",".$s_longitude."&destinations=".$d_latitude.",".$d_longitude."&mode=driving&sensor=false&units=imperial&key=".config('constants.map_key');

            $client = new \GuzzleHttp\Client;
            $location = $client->get($apiurl);
            $location = json_decode($location->getBody(),true);

            if(!empty($location['rows'][0]['elements'][0]['status']) && $location['rows'][0]['elements'][0]['status']=='OK'){

                $meters = $location['rows'][0]['elements'][0]['distance']['value'];
                $source = $s_latitude . ',' . $s_longitude;
                $destination = $d_latitude . ',' . $d_longitude;
                $minutes = $location['rows'][0]['elements'][0]['duration']['value'];

            }

            return response()->json(['meters' => $meters, 'source' => $source, 'destination' => $destination, 'minutes' => $minutes, 'marker' => $ride->marker ]);
        }

        return response()->json([ 'status' => 'Data not available' ], 201);
    }

    public function requestHistory()
    {
        $provider = auth()->user('provider');

        $requests = UserRequest::where('provider_id', $provider->id)
            ->latest()
            ->paginate();

        return view('provider.request.requestHistory', compact('requests'));
    }

    public function showRequest(UserRequest $userRequest)
    {
        return view('provider.requestDetail_template', compact('userRequest'));
    }
}

// Wallet
// Summary 
// Earnings 
