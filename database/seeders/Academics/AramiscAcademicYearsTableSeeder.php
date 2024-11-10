<?php

namespace Database\Seeders\Academics;

use App\AramiscAcademicYear;
use App\AramiscSection;
use Illuminate\Database\Seeder;

class AramiscAcademicYearsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id = 1, $count = 10)
    {
        AramiscAcademicYear::factory()->times($count)->create([
            'school_id' => $school_id
        ]);
    }
}
