<?php

namespace Database\Seeders\Academics;

use App\AramiscClassRoom;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AramiscClassRoomsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count = 5)
    {
        AramiscClassRoom::factory()->times($count)->create([
            'school_id' => $school_id,
            'academic_id' => $academic_id
        ]);

    }
}
