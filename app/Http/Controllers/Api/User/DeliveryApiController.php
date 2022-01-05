<?php

namespace App\Http\Controllers\Api\User;

// use DB;
// use Log;
// use Route;
// use Notification;
// use App\Models\Time;
use Auth;
// use App\Notifications\WebPush;

use Exception;
use App\Models\Provider;
use App\Models\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserRequestDelivery;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeliveryApiController extends Controller
{
    public function buff_addDLocation(Request $request)
    {
        if ($request->ajax()) {
            $this->validate($request, [
                's_latitude'   => 'required|numeric',
                's_longitude'  => 'required|numeric',
                'd_latitude'   => 'numeric',
                'd_longitude'  => 'numeric',
                'service_type' => 'required|numeric|exists:service_types,id',
                //'promocode_id' => 'sometimes|exists:promocodes,id',
                'distance'             => 'numeric',
                'use_wallet'           => 'numeric',
                'estimated_fare'       => 'sometimes|numeric',
                'service_required'     => 'required|in:none,rental,outstation,delivery',
                //TODO ALLAN - Alterações débito na máquina e voucher
                'payment_mode' => 'required|in:BRAINTREE,CASH,DEBIT_MACHINE,CARD,PAYPAL,PAYPAL-ADAPTIVE,PAYUMONEY,PAYTM',

                'card_id' => ['required_if:payment_mode,CARD', 'exists:cards,card_id,user_id,' . auth()->user()->id],
            ]);
        } else {
            $this->validate($request, [
                's_latitude'           => 'required|numeric',
                's_longitude'          => 'required|numeric',
                'd_latitude'           => 'numeric',
                'd_longitude'          => 'numeric',
                'service_type'         => 'required|numeric|exists:service_types,id',
                'service_required'     => 'required|in:none,rental,outstation,delivery',
                //'promocode_id' => 'sometimes|exists:promocodes,id',
                'distance'   => 'required|numeric',
                'use_wallet' => 'numeric',

                //TODO ALLAN - Alterações débito na máquina e voucher
                'payment_mode' => 'required|in:BRAINTREE,CASH,CARD,DEBIT_MACHINE,PAYPAL,PAYPAL-ADAPTIVE,PAYUMONEY,PAYTM',

                'card_id' => ['required_if:payment_mode,CARD', 'exists:cards,card_id,user_id,' . auth()->user()->id],
            ]);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function addDLocation(Request $request)
    {
        $this->validate($request, [
            'request_id'         => 'required|integer|exists:user_requests,id,user_id,' . auth()->user()->id,
            'latitude'           => 'required|nullable|numeric',
            'longitude'          => 'required|nullable|numeric',
            'address'            => 'required|nullable',
            'comments'           => 'sometimes|nullable',
            'id'                 => 'sometimes|nullable',
            'image'              => 'sometimes|file|max:1024',
            'weight'             => 'sometimes|nullable',
            'product_type_id'    => 'required|integer',
        ]);

        try {
            $UserRequest = UserRequest::findOrFail($request->request_id);

            $UserRequest->save();

            if ($request->hasFile('image')) {
                $request->image = $request->file('image')->store('public/delivery/image');
            }
            if ($request->has('id')) {
                $data = [
                    'user_id'         => $UserRequest->user_id,
                    'product_type_id' => $request->product_type_id,
                    'weight'          => $request->weight ?? 0,
                    'image'           => $request->image,
                    'comments'        => $request->comments,
                    'latitude'        => $request->latitude,
                    'longitude'       => $request->longitude,
                    'address'         => $request->address,
                ];
            } else {
                $data = [
                    'user_id'         => $UserRequest->user_id,
                    'product_type_id' => $request->product_type_id,
                    'weight'          => $request->weight ?? 0,
                    'image'           => $request->image,
                    'comments'        => $request->comments,
                    'latitude'        => $request->latitude,
                    'longitude'       => $request->longitude,
                    'address'         => $request->address,
                    'otp'             => mt_rand(1000, 9999),
                ];
            }
            $UserRequest->delivery()->updateOrCreate(['id' => $request->id], $data);
            // Send Push Notification to Provider
            if ($request->ajax()) {
                if ($request->has('id')) {
                    return response()->json(['message' => trans('api.ride.delivery_updated')]);
                } else {
                    return response()->json(['message' => trans('api.ride.delivery_added')]);
                }
            } else {
                if ($request->has('id')) {
                    return response()->json(['message' => trans('api.ride.delivery_updated')]);
                } else {
                    return redirect('dashboard')->with('flash_success', trans('api.ride.delivery_added'));
                }
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong') . $e], 500);
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
    public function removeDLocation(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric|exists:user_request_deliveries',
        ]);

        try {
            $UserRequest = UserRequestDelivery::findOrFail($request->id);
            $UserRequest->delete();

            if ($request->ajax()) {
                return response()->json(['message' => trans('api.ride.delivery_removed')]);
            } else {
                return redirect('dashboard')->with('flash_success', trans('api.ride.delivery_removed'));
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
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function providerUpdateDLocation(Request $request)
    {
        $this->validate($request, [
            'request_id'  => 'required|integer|exists:user_requests,id',
            'status'      => 'required|in:open,pending,delivered',
            'id'          => 'sometimes|nullable',
            'otp'         => 'sometimes|nullable',
        ]);

        try {
            $UserRequest = UserRequest::findOrFail($request->request_id);

            $UserRequest->save();
            $delivery = UserRequestDelivery::findOrFail($request->id);
            if ($request->has('otp') && $delivery->otp != $request->id && $request->status == 'delivered') {
                return response()->json(['message' => trans('api.ride.delivery_fail')], 422);
            }
            $data = [
                'provider_id' => auth()->user()->id,
                'status'      => $request->status,
            ];
            $UserRequest->delivery()->updateOrCreate(['id' => $request->id], $data);
            // Send Push Notification to Provider
            if ($request->ajax()) {
                return response()->json(['message' => trans('api.ride.delivery_updated')]);
            } else {
                return redirect('dashboard')->with('flash_success', trans('api.ride.delivery_updated'));
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong') . $e], 500);
            } else {
                return back()->with('flash_error', trans('api.something_went_wrong'));
            }
        }
    }
}
