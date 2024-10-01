<?php

namespace Database\Seeders\Admin;

use App\AramiscPostalReceive;
use Illuminate\Database\Seeder;

class AramiscPostalReceiveTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=10)
    {
        AramiscPostalReceive::factory()->times($count)->create([
            'school_id'=>$school_id,
            'academic_id'=>$academic_id,
        ]);
    }
}
