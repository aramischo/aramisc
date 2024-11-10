<?php

namespace Database\Seeders;

use App\AramiscAssignSubject;
use App\AramiscExamSetup;
use Illuminate\Database\Seeder;

class aramisc_exam_setupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $data = AramiscAssignSubject::all();
        foreach ($data as $row) {
            $class_id = $row->class_id;
            $section_id = $row->section_id;
            $subject_id = $row->subject_id;
            $s = new AramiscExamSetup();
            $s->class_id = $class_id;
            $s->section_id = $section_id;
            $s->subject_id = $subject_id;
            $s->exam_term_id = 1;
            $s->exam_title = 'Exam';
            $s->exam_mark = 100;
            $s->created_at = date('Y-m-d h:i:s');
            $s->save();
        }

    }
}
