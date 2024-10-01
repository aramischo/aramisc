<?php

namespace Database\Seeders\Academics;

use App\AramiscStaff;
use App\AramiscClassTeacher;
use App\AramiscAssignClassTeacher;
use Illuminate\Database\Seeder;

class AramiscAssignClassTeacherTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id = 1, $academic_id = null, $count = 5)
    {
        $teacher_id = AramiscStaff::where('role_id', 4)->where('school_id', $school_id)->first()->id;
        $AramiscAssignClassTeachers = AramiscAssignClassTeacher::where('school_id', $school_id)->where('academic_id', $academic_id)->get();
        foreach($AramiscAssignClassTeachers as $classTeacher) {
            $store = new AramiscClassTeacher();
            $store->assign_class_teacher_id = $classTeacher->id;
            $store->teacher_id = $teacher_id;
            $store->created_at = date('Y-m-d h:i:s');
            $store->school_id = $school_id;
            $store->academic_id = $academic_id;
            $store->save();
        }
    }
}
