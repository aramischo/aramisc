<?php

namespace Database\Seeders\Fees;

use App\AramiscFeesDiscount;
use Illuminate\Database\Seeder;

class AramiscFeesDiscountTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=5)
    {
        //
        AramiscFeesDiscount::factory()->times($count)->create([
            'school_id'=>$school_id,
            'academic_id'=>$academic_id
        ]);
    }
}
