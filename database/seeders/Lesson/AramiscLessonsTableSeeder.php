<?php

namespace Database\Seeders\Lesson;

use App\AramiscAssignSubject;
use Illuminate\Database\Seeder;
use Modules\Lesson\Entities\AramiscLesson;

class AramiscLessonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=5)
    {
        $assignSubjects = AramiscAssignSubject::where('school_id', $school_id)
        ->where('academic_id', $academic_id)
        ->get();
        $lessons=['Chapter 01','Chapter 02','Chapter 03','Chapter 04','Chapter 05','Chapter 06','Chapter 07','Chapter 08','Chapter 09','Chapter 10','Chapter 11','Chapter 12'];
        foreach($assignSubjects as $classSec){
            foreach($lessons as $lesson){
                $aramiscLesson=new AramiscLesson;
                $aramiscLesson->lesson_title=$lesson.'.'.$classSec->id;
                $aramiscLesson->class_id=$classSec->class_id;	
                $aramiscLesson->subject_id=$classSec->subject_id;
                $aramiscLesson->section_id=$classSec->section_id;
                $aramiscLesson->school_id=$school_id;
                $aramiscLesson->academic_id=$academic_id;
                $aramiscLesson->save();

            }
        } 
    }
}
