<?php

namespace Database\Seeders\HumanResources;

use App\AramiscDesignation;
use Illuminate\Database\Seeder;

class AramiscDesignationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id = 1, $count= 1)
    {
        AramiscDesignation::factory()->times($count)->create([
            'school_id' => $school_id
        ]);
    }
}
