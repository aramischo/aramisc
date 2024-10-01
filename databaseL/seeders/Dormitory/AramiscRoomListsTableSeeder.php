<?php

namespace Database\Seeders\Dormitory;

use App\AramiscRoomList;
use Illuminate\Database\Seeder;

class AramiscRoomListsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $count = 5)
    {
        AramiscRoomList::factory()->times($count)->create([
            'school_id' => $school_id
        ]);
    }
}
