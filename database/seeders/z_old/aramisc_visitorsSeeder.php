<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\AramiscVisitor;

class aramisc_visitorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id = 1)
    {
        AramiscVisitor::factory()->times(10)->create([
            'school_id' => $school_id,
        ]);       
    }
}
