<?php

namespace Database\Seeders\Dormitory;

use App\AramiscRoomType;
use Illuminate\Database\Seeder;

class AramiscRoomTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $count = 5)
    {
        AramiscRoomType::factory()->times($count)->create([
            'school_id' => $school_id
        ]);
    }
}
