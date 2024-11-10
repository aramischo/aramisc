<?php

namespace Database\Seeders;

use App\AramiscHumanDepartment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class aramisc_human_departmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      AramiscHumanDepartment::factory()->times(10)->create();
    }
}
