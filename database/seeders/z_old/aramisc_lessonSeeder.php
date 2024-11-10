<?php

namespace Database\Seeders;

use App\AramiscAssignSubject;
use Illuminate\Database\Seeder;
use Modules\Lesson\Entities\AramiscLesson;

class aramisc_lessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $sections=AramiscAssignSubject::where('class_id',1)
                        ->where('subject_id',1)
                        ->get();
        $lessons=['Chapter 01','Chapter 02','Chapter 03','Chapter 04','Chapter 05','Chapter 06','Chapter 07','Chapter 08','Chapter 09','Chapter 10','Chapter 11','Chapter 12'];
        foreach($sections as $section){
            foreach($lessons as $lesson){
                $aramiscLesson=new AramiscLesson;
                $aramiscLesson->lesson_title=$lesson;
                $aramiscLesson->class_id=1;	
                $aramiscLesson->subject_id=1;
                $aramiscLesson->section_id=$section->section_id;
                $aramiscLesson->save();
                
            }
        } 
    }
}
