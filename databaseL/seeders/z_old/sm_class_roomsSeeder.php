<?php

namespace Database\Seeders;

use App\AramiscClassRoom;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class sm_class_roomsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // AramiscClassRoom::truncate();
        AramiscClassRoom::factory()->times(5)->create();

    }
}
