<?php

namespace Database\Seeders;

use App\AramiscFeesCarryForward;
use App\AramiscStudent;
use Illuminate\Database\Seeder;

class sm_fees_carry_forwardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        AramiscFeesCarryForward::query()->truncate();
        $students = AramiscStudent::where('class_id', 1)->get();
        foreach ($students as $student){
            $store = new AramiscFeesCarryForward();
            $store->student_id = $student->id;
            $store->balance = rand(1000,5000);
            $store->save();
        }
    }
}
