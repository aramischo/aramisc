<?php

namespace Database\Seeders\Transport;

use App\AramiscRoute;
use Illuminate\Database\Seeder;

class AramiscRoutesTableSeeder extends Seeder
{
    public function run($school_id = 1, $academic_id = 1, $count = 5){
        AramiscRoute::factory()->times($count)->create([
           'school_id' => $school_id,
           'academic_id' => $academic_id,
        ]);
    }

}