<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\AramiscIncomeHead;

class sm_income_headsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      AramiscIncomeHead::factory()->times(5)->create();        
    }
}
