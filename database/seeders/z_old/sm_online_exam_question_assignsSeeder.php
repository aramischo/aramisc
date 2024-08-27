<?php

namespace Database\Seeders\OnlineExam;

use App\AramiscOnlineExam;
use App\AramiscQuestionBank;
use Illuminate\Database\Seeder;
use App\AramiscOnlineExamQuestionAssign;

class AramiscOnlineExamQuestionAssignTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count)
    {
        $online_exams = AramiscOnlineExam::where('school_id', $school_id)->where('academic_id', $academic_id)->take(10)->get();
        foreach ($online_exams as $online_exam){
            $question_banks = AramiscQuestionBank::where('school_id', $school_id)->where('academic_id', $academic_id)->take(10)->get();
            foreach ($question_banks as $question_bank) {
                $store = new AramiscOnlineExamQuestionAssign();
                $store->online_exam_id = $online_exam->id;
                $store->question_bank_id = $question_bank->id;
                $store->created_at = date('Y-m-d h:i:s');
                $store->school_id = $school_id;
                $store->academic_id = $academic_id;
                $store->save();
            }

        }
    }
}
