<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\AramiscHourlyRate;

class aramisc_hourly_ratesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        for($i=1; $i<=5; $i++){
            $store= new AramiscHourlyRate();
            $store->grade="A+";
            $store->rate=20;
            $store->save();

        }
    }
}
