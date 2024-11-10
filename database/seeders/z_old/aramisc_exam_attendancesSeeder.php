<?php

namespace Database\Seeders;

use App\AramiscExamAttendance;
use Illuminate\Database\Seeder;

class aramisc_exam_attendancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        AramiscExamAttendance::query()->truncate();
        for($i=1; $i<=3; $i++){
            $store= new AramiscExamAttendance();
            $store->exam_id=$i;
            $store->subject_id=$i;
            $store->class_id=$i;
            $store->section_id=1;
            $store->created_by=1;
            $store->created_at = date('Y-m-d h:i:s');
            $store->save();
        }
    }
}
