<?php

namespace Database\Seeders\Exam;

use App\AramiscAssignSubject;
use App\AramiscExam;
use App\AramiscExamSetup;
use App\AramiscExamType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AramiscExamTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count = 3)
    {
        AramiscExamType::factory()->times($count)->create([
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ])->each(function($exam_type){
            $data = AramiscAssignSubject::withOutGlobalScopes()->where([
                'school_id' => $exam_type->school_id, 
                'academic_id' => $exam_type->academic_id])->get();
            foreach ($data as $row) {
                $s = new AramiscExamSetup();
                $s->class_id = $row->class_id;
                $s->section_id = $row->section_id;
                $s->subject_id = $row->subject_id;
                $s->exam_term_id = $exam_type->id;
                $s->school_id = $exam_type->school_id;
                $s->academic_id = $exam_type->academic_id;
                $s->exam_title = 'Exam';
                $s->exam_mark = 100;
                $s->created_at = date('Y-m-d h:i:s');
                $s->save();

                AramiscExam::create([
                    'exam_type_id' => $exam_type->id,
                    'school_id' => $exam_type->school_id,
                    'class_id' => $row->class_id,
                    'section_id' => $row->section_id,
                    'subject_id' => $row->subject_id,
                    'exam_mark' => 100,
                    'academic_id' =>$exam_type->academic_id,
                    'active_status' => 1,
                ]);
            }


        });
    }
}
