<?php

namespace Database\Seeders;

use App\Models\GeoFencing;
use Illuminate\Database\Seeder;

class GeoFencingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GeoFencing::factory()
            ->count(1)
            ->create();

            for ($i=5; $i < 13; $i++) { 
                \App\Models\ServiceTypeGeoFencing::create([
                    'geo_fencing_id'   => 1,
                    'service_type_id'  => $i,
                    'fixed'            => 20,
                    'price'            => 10,
                    'status'           => 1,
                    'minute'           => 0,
                    'hour'             => '0',
                    'city_limits'      => 100,
                    'distance'         => '1',
                ]);
            }
    }
    
}
