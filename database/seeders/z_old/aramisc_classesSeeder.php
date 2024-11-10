<?php

namespace Database\Seeders;

use App\AramiscClass;
use Illuminate\Database\Seeder;

class aramisc_classesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {    
        AramiscClass::factory()->times(10)->create(); 
    }
}
