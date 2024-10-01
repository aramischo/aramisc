<?php

namespace Database\Seeders\Academics;

use App\AramiscSubject;
use Illuminate\Database\Seeder;

class AramiscSubjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id = 1, $academic_id=1, $count = 10)
    {
        AramiscSubject::factory()->times($count)->create([
            'school_id' => $school_id,
            'academic_id' => $academic_id
        ]);
    }
}
