<?php

namespace Database\Seeders;

use App\AramiscSection;
use Illuminate\Database\Seeder;


class aramisc_sectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

       
        AramiscSection::factory()->times(5)->create();
     
    }
}
