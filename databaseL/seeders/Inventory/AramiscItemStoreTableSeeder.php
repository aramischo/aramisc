<?php

namespace Database\Seeders\Inventory;

use App\AramiscItemStore;
use Illuminate\Database\Seeder;

class AramiscItemStoreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=5)
    {
        $school_academic=[
            'school_id'=>$school_id,
            'academic_id'=>$academic_id,
        ];
        AramiscItemStore::factory()->times($count)->create($school_academic);

    }
}
