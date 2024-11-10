<?php

namespace Database\Seeders;

use App\AramiscSubject;
use Illuminate\Database\Seeder;

class aramisc_subjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AramiscSubject::factory()->times(10)->create();
    }
}
