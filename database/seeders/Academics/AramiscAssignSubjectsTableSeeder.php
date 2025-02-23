<?php

namespace Database\Seeders\Academics;

use App\AramiscStaff;
use App\AramiscSubject;
use App\AramiscClassSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AramiscAssignSubjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id)
    {
        $teacher = AramiscStaff::where('role_id', 4)->where('school_id', $school_id)->pluck('id')->unique();
        if($teacher){
            $data = AramiscClassSection::where('school_id', $school_id)->where('academic_id', $academic_id)->get();
        $subject_id = AramiscSubject::where('school_id', $school_id)->where('academic_id', $academic_id)->pluck('id')->unique();
        foreach ($data as $datum) {
            $class_id = $datum->class_id;
            $section_id = $datum->section_id;
            foreach ($subject_id as $subject) {
                DB::table('aramisc_assign_subjects')->insert([
                    [
                        'class_id' => $class_id,
                        'section_id' => $section_id,
                        'teacher_id' => $teacher[random_int(0,count($teacher)-1)] ?? $teacher[0],
                        'subject_id' => $subject,
                        'created_at' => date('Y-m-d h:i:s'),
                        'school_id'  => $school_id,
                        'academic_id'  => $academic_id,
                    ]
                ]);
            }
        }
        }
    }
}
