<?php

namespace Database\Seeders\Fees;

use App\AramiscClassSection;
use App\AramiscFeesCarryForward;
use App\Models\StudentRecord;
use Illuminate\Database\Seeder;

class AramiscFeesCarryForwardTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=5)
    {
        $classSection = AramiscClassSection::where('school_id',$school_id)->where('academic_id', $academic_id)->first();
        $students = StudentRecord::where('class_id', $classSection->class_id)->where('section_id', $classSection->section_id)->where('school_id',$school_id)->where('academic_id', $academic_id)->get();
        foreach ($students as $student){
            $store = new AramiscFeesCarryForward();
            $store->student_id = $student->student_id;
            $store->balance = rand(1000,5000);
            $store->save();
        }
    }
}
