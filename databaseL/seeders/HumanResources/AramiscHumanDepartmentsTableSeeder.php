<?php

namespace Database\Seeders\HumanResources;

use App\AramiscHumanDepartment;
use Illuminate\Database\Seeder;

class AramiscHumanDepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id = 1, $count = 10)
    {
        AramiscHumanDepartment::factory()->times($count)->create([
            'school_id' => $school_id
        ]);
    }
}
