<?php

namespace Database\Seeders;

use App\AramiscFeesAssign;
use App\AramiscFeesMaster;
use App\AramiscStudent;
use Illuminate\Database\Seeder;

class aramisc_fees_assignsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        AramiscFeesAssign::query()->truncate();
        $students = AramiscStudent::where('active_status', 1)->where('class_id', 1)->get();
        foreach ($students as $student) {
            $val = 1 + rand() % 5;
            $fees_masters = AramiscFeesMaster::where('active_status', 1)->take($val)->get();
            foreach ($fees_masters as $fees_master) {
                $store = new AramiscFeesAssign();
                $store->student_id = $student->id;
                $store->fees_master_id = $fees_master->id;
                $store->save();
            }
        }
    }
}
