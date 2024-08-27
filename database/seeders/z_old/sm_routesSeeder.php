<?php

namespace Database\Seeders;

use App\AramiscRoute;
use App\AramiscStaff;
use Illuminate\Database\Seeder;

class sm_routesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $drivers = AramiscStaff::whereRole(9)->where('active_status', 1)->get();
        foreach ($drivers as $driver) {
            $store = new AramiscRoute();
            $store->title = 'Transport Route ' . $driver->id;
            $store->far = rand(100, 500);
            $store->created_at = date('Y-m-d h:i:s');
            $store->save();
        }
    }
}
