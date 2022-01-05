<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //DB::table('service_types')->truncate();
        DB::table('service_peak_hours')->truncate();
        DB::table('service_types')->insert([
            [
                'name'          => '{"en":"Electrician"}',
                'parent_id'     => 0,
                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'calculator'    => 'FIXED',
                'marker'        => asset('img/cars/sedan_marker.png'),
                'image'         => url('img/services/electrician.jpg'),
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Plumbing"}',
                'parent_id'     => 0,
                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'calculator'    => 'FIXED',
                'marker'        => asset('img/cars/sedan_marker.png'),
                'image'         => url('img/services/plumbing.jpg'),
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Carpenter"}',
                'parent_id'     => 0,
                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'calculator'    => 'FIXED',
                'marker'        => asset('img/cars/sedan_marker.png'),
                'image'         => url('img/services/carpenter.jpg'),
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Electrician"}',
                'parent_id'     => 0,
                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'calculator'    => 'FIXED',
                'marker'        => asset('img/cars/sedan_marker.png'),
                'image'         => url('img/services/mechanic.jpg'),
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Electrician - 1"}',
                'parent_id'     => 1,
                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'calculator'    => 'FIXED',
                'marker'        => asset('img/cars/sedan_marker.png'),
                'image'         => url('img/services/electrician.jpg'),
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Electrician -2"}',
                'parent_id'     => 1,
                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'calculator'    => 'FIXED',
                'marker'        => asset('img/cars/sedan_marker.png'),
                'image'         => url('img/services/plumbing.jpg'),
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Plumber -1"}',
                'parent_id'     => 2,
                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'calculator'    => 'FIXED',
                'marker'        => asset('img/cars/sedan_marker.png'),
                'image'         => url('img/services/carpenter.jpg'),
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Plumber -2"}',
                'parent_id'     => 2,
                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'calculator'    => 'FIXED',
                'marker'        => asset('img/cars/sedan_marker.png'),
                'image'         => url('img/services/mechanic.jpg'),
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Carpenter - 1"}',
                'parent_id'     => 3,
                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'calculator'    => 'FIXED',
                'marker'        => asset('img/cars/sedan_marker.png'),
                'image'         => url('img/services/electrician.jpg'),
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Carpenter -2"}',
                'parent_id'     => 3,
                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'calculator'    => 'FIXED',
                'marker'        => asset('img/cars/sedan_marker.png'),
                'image'         => url('img/services/plumbing.jpg'),
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Mechanic -1"}',
                'parent_id'     => 4,
                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'calculator'    => 'FIXED',
                'marker'        => asset('img/cars/sedan_marker.png'),
                'image'         => url('img/services/carpenter.jpg'),
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Mechanic -2 "}',
                'parent_id'     => 4,
                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'calculator'    => 'FIXED',
                'marker'        => asset('img/cars/sedan_marker.png'),
                'image'         => url('img/services/mechanic.jpg'),
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
        ]);
    }
}
