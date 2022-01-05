<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Admin::factory()
            ->count(1)
            ->create([
                'name'     => 'Admin Admin',
                'email'    => 'admin@dragon.com',
                'password' => Hash::make('password'),
            ]);

        \App\Models\User::factory()
        ->count(1)
        ->create([
            'first_name' => 'User',
            'last_name'  => 'Dragon',
            'email'      => 'user@dragon.com',
            'password'   => Hash::make('password'),
        ]);

        \App\Models\Provider::factory()
        ->count(1)
        ->create([
            'first_name'  => 'User',
            'last_name'   => 'Provider',
            'email'       => 'partner@dragon.com',
            'status'      => 'approved',
            'password'    => Hash::make('password'),
        ]);

        DB::table('provider_services')->insert([[
            'provider_id'     => 1,
            'service_type_id' => 5,
            'status'          => 'active',
        ],
        [
            'provider_id'     => 1,
            'service_type_id' => 6,
            'status'          => 'active',
        ]
        ]);

        \App\Models\Agent::factory()
            ->count(1)
            ->create([
                'name'     => 'User Agent',
                'email'    => 'agent@dragon.com',
                'password' => Hash::make('password'),
            ]);

        \App\Models\Setting::create([
            'key'   => 'demo_mode',
            'value' => '1',
        ]);

        //$this->call(UserSeeder::class);
        //$this->call(ProviderSeeder::class);
        $this->call(ServiceTypeSeeder::class);
        $this->call(GeoFencingSeeder::class);
        // $this->call(AgentSeeder::class);
        $this->call(PermissionsSeeder::class);
        // $this->call(ExtraSeeder::class);
        $this->call(DocumentSeeder::class);
        // $this->call(PromocodeSeeder::class);
        $this->call(CancelReasonSeeder::class);
        // $this->call(UserRequestPaymentSeeder::class);
        
    }
}
