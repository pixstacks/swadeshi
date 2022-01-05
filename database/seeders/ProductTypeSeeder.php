<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('product_types')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('product_types')->insert([
            [
                'name'          => '{"en":"Document"}',
                'icon'         => 'public/asset/img/cars/sedan.png',
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Goods"}',
                'icon'         => 'public/asset/img/cars/sedan.png',
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Medicine"}',
                'icon'         => 'public/asset/img/cars/sedan.png',
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Grocery"}',
                'icon'         => 'public/asset/img/cars/sedan.png',
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
        ]);
    }
}
