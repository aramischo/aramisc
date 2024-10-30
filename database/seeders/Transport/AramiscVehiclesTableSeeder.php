<?php

namespace Database\Seeders\Transport;

use App\AramiscVehicle;
use Illuminate\Database\Seeder;

class AramiscVehiclesTableSeeder extends Seeder
{
    public function run($school_id = 1, $count = 5){

        AramiscVehicle::factory()->times($count)->create([
            'school_id' => $school_id
        ]);
    }

}