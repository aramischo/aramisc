<?php

namespace Database\Seeders;

use App\AramiscStaff;
use App\AramiscClassSection;
use App\AramiscAssignSubject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
// use DB;

class aramisc_assign_subjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //        AramiscAssignSubject::query()->truncate();
        $teacher = AramiscStaff::where('role_id', 4)->first();
        $data = AramiscClassSection::where('class_id', 1)->get();
        $subject_id = [1, 2, 3];
        foreach ($data as $datum) {
            $class_id = $datum->class_id;
            $section_id = $datum->section_id;
            foreach ($subject_id as $subject) {

                DB::table('aramisc_assign_subjects')->insert([
                    [
                        'class_id' => $class_id,
                        'section_id' => $section_id,
                        'teacher_id' => $teacher->id,
                        'subject_id' => $subject,
                        'created_at' => date('Y-m-d h:i:s')
                    ]
                ]);
            }
        }
    }
}
