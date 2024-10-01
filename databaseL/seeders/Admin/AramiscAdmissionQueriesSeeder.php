<?php

namespace Database\Seeders\Admin;

use App\AramiscAdmissionQuery;
use Illuminate\Database\Seeder;

class AramiscAdmissionQueriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id = 1, $academic_id = 1, $count = 10)
    {
        AramiscAdmissionQuery::factory()->times($count)->create([
            'class' => 1,
            'school_id' => $school_id,
            'academic_id' => $academic_id
        ]);

    }
}
