<?php

namespace Database\Seeders;

use App\AramiscAssignSubject;
use App\AramiscClass;
use App\AramiscClassSection;
use App\AramiscExamSchedule;
use App\AramiscSection;
use Illuminate\Database\Seeder;

class aramisc_exam_schedulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

//        AramiscExamSchedule::query()->truncate();

        $classes = AramiscClass::where('active_status',1)->get();
        foreach ($classes as $class) {
            $sections = AramiscClassSection::where('class_id', $class->class_id)->get();
            foreach ($sections as $section) {
                $subjects = AramiscAssignSubject::where('class_id', $class->class_id)->where('section_id', $section->section_id)->get();
                foreach ($subjects as $subject) {
                    $s = new AramiscExamSchedule();
                    $s->class_id = $class->class_id;
                    $s->section_id = $section->section_id;
                    $s->subject_id = $subject->subject_id;
                    $s->exam_term_id = 1;
                    $s->exam_id = 1;
                    $s->exam_period_id = $section->section_id;
                    $s->created_at = date('Y-m-d h:i:s');
                    $s->save();
                }


            }
        }
    }
}
