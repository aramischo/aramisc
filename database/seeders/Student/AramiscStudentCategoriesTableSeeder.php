<?php

namespace Database\Seeders\Student;

use App\AramiscStudentCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AramiscStudentCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $count = 6){
        AramiscStudentCategory::factory()->times($count)->create([
            'school_id' => $school_id,
        ]);
    }
}
