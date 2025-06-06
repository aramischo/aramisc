<?php

namespace Database\Seeders\Academics;

use App\AramiscSection;
use Illuminate\Database\Seeder;

class AramiscSectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id = 1, $academic_id = null, $count = 5)
    {
        AramiscSection::factory()->times($count)->create([
            'school_id' => $school_id,
            'academic_id' => $academic_id
        ]);
    }
}
