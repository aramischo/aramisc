<?php

namespace Database\Seeders\Admin;

use App\AramiscPostalDispatch;
use Illuminate\Database\Seeder;

class AramiscPostalDispatchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=10)
    {
        AramiscPostalDispatch::factory()->times($count)->create([
            'school_id'=>$school_id,
            'academic_id'=>$academic_id,
        ]);
    }
}
