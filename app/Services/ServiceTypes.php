<?php

namespace App\Services;

use Auth;
use Exception;
use Validator;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\PeakHour;
use App\Models\Provider;
use App\Models\GeoFencing;
use App\Models\ServiceType;
use App\Models\ProviderService;
use App\Models\ServicePeakHour;
use Illuminate\Support\Facades\Log;

class ServiceTypes
{
    public function __construct()
    {
    }

    /**
        * Get a validator for a tradepost.
        *
        * @param  array $data
        * @return \Illuminate\Contracts\Validation\Validator
    */
    protected function validator(array $data)
    {
        $rules = [
            'location'  => 'required',
        ];

        $messages = [
            'location.required' => 'Location Required!',
        ];

        return Validator::make($data, $rules, $messages);
    }

    /**
    * get the btc details.
    * get the currency master data.
    * get the payment methods master data.
    * @return response with data,system related errors
    */
    public function show()
    {
    }

    /**
        * get all details.
        * @return response with data,system related errors
    */
    public function getAll()
    {
    }

    /**
        * find tradepost.
        * @param  $id
        * @return response with data,system related errors
    */
    public function find($id)
    {
    }

    /**
        * insert function
        * checking form field validations
        * @param  $postrequest
        * @return response with success,errors,system related errors
    */
    public function create($request)
    {
    }

    /**
        * Update function
        * checking form validations
        * @param  $postrequest
        * @return response with success,errors,system related errors
    */
    public function update($request, $id)
    {
    }

    /**
        * Delete function.
        * @param  $id
        * @return response with success,errors,system related errors
    */
    public function delete($id)
    {
    }

    public function calculateFare($request, $cflag=0)
    {
        try {
            $total   =$tax_price   ='';
            $location=$this->getLocationDistance($request);

            if (!empty($location['errors'])) {
                throw new Exception($location['errors']);
            } else {
                if (config('constants.distance', 'Kms') == 'Kms') {
                    $total_kilometer = round($location['meter'] / 1000, 1);
                } //TKM
                else {
                    $total_kilometer = round($location['meter'] / 1609.344, 1);
                } //TMi

                $requestarr['meter']       =$total_kilometer;
                $requestarr['time']        =$location['time'];
                $requestarr['seconds']     =$location['seconds'];
                $requestarr['kilometer']   =0;
                $requestarr['minutes']     =0;
                $requestarr['service_type']=$request['service_type'];

                $tax_percentage        = config('constants.tax_percentage');
                $commission_percentage = config('constants.commission_percentage');
                $surge_trigger         = config('constants.surge_trigger');

                $price_response=$this->applyPriceLogic($requestarr);

                if ($tax_percentage > 0) {
                    $tax_price        = $this->applyPercentage($price_response['price'], $tax_percentage);
                    $commission_price = $this->applyPercentage($price_response['price'], $commission_percentage);
                    $total            = $price_response['price'] + $tax_price;
                } else {
                    $commission_price = $this->applyPercentage($price_response['price'], $commission_percentage);
                    $total            = $price_response['price'];
                }

                if ($cflag != 0) {
                    if ($commission_percentage > 0) {
                        $commission_price = $this->applyPercentage($price_response['price'], $commission_percentage);
                        $commission_price = $price_response['price'];
                    }

                    $surge = 0;

                    /*if ($surge_trigger>0) {

                        $ActiveProviders = ProviderService::AvailableServiceProvider($request['service_type'])->get()->pluck('provider_id');

                        $distance = config('constants.provider_search_radius', '10');
                        $latitude = $request['s_latitude'];
                        $longitude = $request['s_longitude'];

                        $Providers = Provider::whereIn('id', $ActiveProviders)
                            ->where('status', 'approved')
                            ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                            ->get();

                        $surge = 0;

                        if ($Providers->count() <= config('constants.surge_trigger') && $Providers->count() > 0) {
                            $surge_price = $this->applyPercentage($total,config('constants.surge_percentage'));
                            $total += $surge_price;
                            $surge = 1;
                        }

                    }

                    $surge_percentage = 1+(config('constants.surge_percentage')/100)."X";*/

                    $start_time = Carbon::now()->toTimeString();

                    $start_time_check = PeakHour::where('start_time', '<=', $start_time)->where('end_time', '>=', $start_time)->first();

                    $surge_percentage = 1 + (0 / 100) . 'X';

                    if ($start_time_check) {
                        $Peakcharges = ServicePeakHour::where('service_type_id', $request['service_type'])->where('peak_hours_id', $start_time_check->id)->first();

                        if ($Peakcharges) {
                            $surge_price=($Peakcharges->min_price / 100) * $total;
                            $total += $surge_price;
                            $surge            = 1;
                            $surge_percentage = 1 + ($Peakcharges->min_price / 100) . 'X';
                        }
                    }
                }

                $return_data['estimated_fare']=$this->applyNumberFormat(floatval($total));
                $return_data['distance']      =$total_kilometer;
                $return_data['time']          =$location['time'];
                $return_data['tax_price']     =$this->applyNumberFormat(floatval($tax_price));
                $return_data['base_price']    =$this->applyNumberFormat(floatval($price_response['base_price']));
                $return_data['service_type']  =(int)$request['service_type'];
                $return_data['service']       =$price_response['service_type'];

                if (auth()->user()) {
                    $return_data['surge']         =$surge;
                    $return_data['surge_value']   =$surge_percentage;
                    $return_data['wallet_balance']=$this->applyNumberFormat(floatval(auth()->user()->wallet_balance));
                }

                $service_response['data']=$return_data;
            }
        } catch (Exception $e) {
            $service_response['errors']=$e->getMessage();
        }

        return $service_response;
    }

    public function applyPriceLogic($requestarr, $iflag=0)
    {
        $fn_response  = [];
        \Log::alert($requestarr);
        $service_type = ServiceType::findOrFail($requestarr['service_type']);
        if ($iflag == 0) {
            //for estimated fare
            $kilometer     = $total_kilometer     = $requestarr['meter']; //TKM || TMi
            $total_minutes = round($requestarr['seconds'] / 60); //TM
            $total_hours   =($requestarr['seconds'] / 60) / 60; //TH
        } else {
            //for invoice fare
            $kilometer     = $total_kilometer     = $requestarr['kilometer']; //TKM || TMi
            $total_minutes = $requestarr['minutes']; //TM
            $total_hours   = $requestarr['minutes'] / 60; //TH
        }
        $base_price=$service_type->fixed; //BP

        //$rental = ceil($requestarr['rental_hours']);

        $geo_fencing=$this->poly_check_new((round($requestarr['s_latitude'], 6)), (round($requestarr['s_longitude'], 6)));
        //return $geo_fencing;
        if ($geo_fencing) {
            $service_type_id          = $requestarr['service_type'];
            $geo_fencing_service_type = GeoFencing::with(
                ['service_geo_fencing' => function ($query) use ($service_type_id) {
                        $query->where('service_type_id', $service_type_id);
                    }]
            )->whereid($geo_fencing)->first();
            \Log::alert($geo_fencing_service_type);
            $service_type = $geo_fencing_service_type->service_geo_fencing;
            if (empty($service_type)) {
                return response()->json(['error' => trans('api.ride.no_service_in_area')], 500);
            }
            ////////---------------Peak Time Calculation--------------------//////////

            //// peak Time Variable

            $per_minute       =$service_type->price; //PM
                $per_hour     =$service_type->price; //PH
                $per_kilometer=$service_type->price; //PKM
                $base_distance=$service_type->distance; //BD
                $min_price    =$service_type->min_price; //BP
                //TODO ALLAN - Tarifa mínima
                $minutes = 0;
        //////// -----------------Peak Time Calculation ------------ /////////
        } else {
            //return response()->json(['error' => trans('api.ride.no_service_in_area') ], 500);
            $fixed_price_only = ServiceType::findOrFail($requestarr['service_type']);
        }
        $fixed_price_only = ServiceType::findOrFail($requestarr['service_type']);
        $price            = $fixed_price_only->fixed;
        if ($fixed_price_only->calculator == 'MIN') {
            //BP+(TM*PM)
            $price = $base_price + ($total_minutes * $per_minute);
        } elseif ($fixed_price_only->calculator == 'FIXED') {
            //BP+(TH*PH)
            $price = $base_price + ($per_hour);
        } elseif ($fixed_price_only->calculator == 'HOUR') {
            //BP+(TH*PH)
            $price = $base_price + ($total_hours * $per_hour);
        } elseif ($fixed_price_only->calculator == 'DISTANCE') {
            //BP+((TKM-BD)*PKM)
            if ($base_distance > $total_kilometer) {
                $price = $base_price;
            } else {
                $price = $base_price + (($total_kilometer - $base_distance) * $per_kilometer);
            }
        } elseif ($fixed_price_only->calculator == 'DISTANCEMIN') {
            //BP+((TKM-BD)*PKM)+(TM*PM)
            if ($base_distance > $total_kilometer) {
                $price = $base_price + ($total_minutes * $per_minute);
            } else {
                $price = $base_price + ((($total_kilometer - $base_distance) * $per_kilometer) + ($total_minutes * $per_minute));
            }
        } elseif ($fixed_price_only->calculator == 'DISTANCEHOUR') {
            //BP+((TKM-BD)*PKM)+(TH*PH)
            if ($base_distance > $total_kilometer) {
                $price = $base_price + ($total_hours * $per_hour);
            } else {
                $price = $base_price + ((($total_kilometer - $base_distance) * $per_kilometer) + ($total_hours * $per_hour));
            }
        } else {
            //by default set Ditance price BP+((TKM-BD)*PKM)
            $price = $base_price;// + (($total_kilometer - $base_distance) * $per_kilometer);
        }

        $service_type_id =$requestarr['service_type'];
        $geo_fencing_id  =$this->poly_check_new((round($requestarr['s_latitude'], 6)), (round($requestarr['s_longitude'], 6)));

        //TODO ALLAN - Tarifa mínima
        if ($price < $min_price) {
            $price = $min_price;
        }

        $fn_response['price']     =$price;
        $fn_response['base_price']=$base_price;
        if ($base_distance > $total_kilometer) {
            $fn_response['distance_fare']=0;
        } else {
            $fn_response['distance_fare']=($total_kilometer - $base_distance) * $per_kilometer;
        }
        $fn_response['minute_fare'] =$total_minutes * $per_minute;
        $fn_response['hour_fare']   =$total_hours * $per_hour;
        $fn_response['calculator']  =$service_type->calculator;
        $fn_response['service_type']=$service_type;
        return $fn_response;
    }

    public function applyPercentage($total, $percentage)
    {
        return ($percentage / 100) * $total;
    }

    public function applyNumberFormat($total)
    {
        return $total;
//        return round($total,config('constants.round_decimal'));
    }

    public function getLocationDistance($locationarr)
    {
        $fn_response=['data'=>null, 'errors'=>null];

        try {
            $s_latitude  = $locationarr['s_latitude'];
            $s_longitude = $locationarr['s_longitude'];
            $d_latitude  = empty($locationarr['d_latitude']) ? $locationarr['s_latitude'] : $locationarr['d_latitude'];
            $d_longitude = empty($locationarr['d_longitude']) ? $locationarr['s_longitude'] : $locationarr['d_longitude'];

            $apiurl = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $s_latitude . ',' . $s_longitude . '&destinations=' . $d_latitude . ',' . $d_longitude . '&mode=driving&sensor=false&units=imperial&key=' . config('constants.map_key');

            $client   = new Client();
            $location = $client->get($apiurl);
            $location = json_decode($location->getBody(), true);

            if (!empty($location['rows'][0]['elements'][0]['status']) && $location['rows'][0]['elements'][0]['status'] == 'ZERO_RESULTS') {
                throw new Exception('Out of service area', 1);
            }
            $fn_response['meter']  =$location['rows'][0]['elements'][0]['distance']['value'];
            $fn_response['time']   =$location['rows'][0]['elements'][0]['duration']['text'];
            $fn_response['seconds']=$location['rows'][0]['elements'][0]['duration']['value'];
        } catch (Exception $e) {
            $fn_response['errors']=trans('user.maperror');
        }

        return $fn_response;
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
        //Log::alert($range_data);

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
