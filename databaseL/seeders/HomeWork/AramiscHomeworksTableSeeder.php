<?php

namespace Database\Seeders\HomeWork;

use App\AramiscHomework;
use App\AramiscAssignSubject;
use Illuminate\Database\Seeder;

class AramiscHomeworksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=10)
    {
        $classSectionSubjects=AramiscAssignSubject::where('school_id',$school_id)->where('academic_id',$academic_id)->get();
        foreach($classSectionSubjects as  $classSectionSubject){ 
            $s = new AramiscHomework();
            $s->class_id =  $classSectionSubject->class_id;
            $s->section_id = $classSectionSubject->section_id;
            $s->subject_id = $classSectionSubject->subject_id;
            $s->homework_date = date('Y-m-d');
            $s->submission_date = date('Y-m-d');
            $s->evaluation_date = date('Y-m-d');
            $s->evaluated_by = 1;
            $s->marks = rand(10, 15);
            $s->description = 'Test';
            $s->created_at = date('Y-m-d h:i:s');
            $s->school_id = $school_id;
            $s->academic_id = $academic_id;
            $s->save();
         }
    }
}
