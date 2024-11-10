<?php

namespace Database\Seeders;

use App\AramiscStudent;

use Illuminate\Database\Seeder;
use App\AramiscOptionalSubjectAssign;
use Illuminate\Support\Facades\DB;
// use DB;

class aramisc_optional_subject_assign extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $students = AramiscStudent::where('class_id', 9)->get();
        if ($students){
            $subjects= DB::table('aramisc_assign_subjects')->select('subject_id')->where('class_id',9)->get()->toArray();
            if($subjects) {
                foreach ($students as $row) {
                    $s = new AramiscOptionalSubjectAssign();
                    $s->student_id = $row->id;
                    $s->session_id = $row->session_id;
                    $s->subject_id = 1;
                    $s->save();
                }

            }
        }
    }
}
