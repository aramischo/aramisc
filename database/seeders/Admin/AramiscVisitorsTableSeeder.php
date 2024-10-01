<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;
use App\AramiscVisitor;

class AramiscVisitorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id = 1, $count = 10)
    {
        AramiscVisitor::factory()->times($count)->create([
            'school_id' => $school_id,
        ]);       
    }
}
