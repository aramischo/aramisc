<?php

namespace Database\Seeders;

use App\AramiscAssignSubject;
use App\AramiscExamMarksRegister;
use App\AramiscStudent;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class aramisc_exam_marks_registersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $students = AramiscStudent::where('class_id', 1)->get();
        foreach ($students as $student) {

            $class_id = $student->class_id;
            $section_id = $student->section_id;
            $subjects = AramiscAssignSubject::where('class_id', $class_id)->where('section_id', $section_id)->get();
            foreach ($subjects as $subject) {
                $store = new AramiscExamMarksRegister();
                $store->exam_id = 1;
                $store->student_id = $student->id;
                $store->subject_id = $subject->subject_id;
                $store->obtained_marks = rand(40, 90);
                $store->exam_date = $faker->dateTime()->format('Y-m-d');
                $store->comments = $faker->realText($maxNbChars = 50, $indexSize = 2);
                $store->created_at = date('Y-m-d h:i:s');
                $store->save();
            } //end subject
        } //end student list
    }
}
