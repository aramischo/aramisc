<?php

namespace Database\Seeders;

use App\AramiscAssignSubject;
use App\AramiscHomework;
use App\AramiscStudent;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class aramisc_homeworksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        AramiscHomework::query()->truncate();
        $students = AramiscStudent::where('class_id', 1)->get();
        $faker = Faker::create();

        foreach ($students as $student) {
            $class_id = $student->class_id;
            $section_id = $student->section_id;
            $subjects = AramiscAssignSubject::where('class_id', $class_id)->where('section_id', $section_id)->get();
            foreach ($subjects as $subject) {
                $s = new AramiscHomework();
                $s->class_id = $class_id;
                $s->section_id = $section_id;
                $s->subject_id = $subject->subject_id;
                $s->homework_date = date('Y-m-d');
                $s->submission_date = date('Y-m-d');
                $s->evaluation_date = date('Y-m-d');
                $s->evaluated_by = 1;
                $s->marks = rand(10, 15);
                $s->description = $faker->text(100);
                $s->created_at = date('Y-m-d h:i:s');
                $s->save();

            }

        }
    }
}
