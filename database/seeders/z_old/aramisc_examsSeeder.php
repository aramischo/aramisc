<?php

namespace Database\Seeders;

use App\AramiscExam;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class aramisc_examsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //        AramiscExam::query()->truncate();

        DB::table('aramisc_exams')->insert([
            [
                'exam_type_id' => 1,
                'school_id' => 1,
                'class_id' => 1,
                'section_id' => 1,
                'subject_id' => 1,
                'exam_mark' => '100',

                'active_status' => 1,
                'created_at' => date('Y-m-d h:i:s')

            ],
            [
                'exam_type_id' => 2,
                'school_id' => 1,
                'class_id' => 1,
                'section_id' => 1,
                'subject_id' => 1,
                'exam_mark' => '100',
                'active_status' => 1,
                'created_at' => date('Y-m-d h:i:s')

            ],
            [
                'exam_type_id' => 3,
                'school_id' => 1,
                'class_id' => 1,
                'section_id' => 1,
                'subject_id' => 1,
                'exam_mark' => '100',
                'active_status' => 1,
                'created_at' => date('Y-m-d h:i:s')

            ]
        ]);
    }
}
