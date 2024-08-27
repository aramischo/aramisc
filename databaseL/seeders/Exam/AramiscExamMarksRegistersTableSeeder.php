<?php

namespace Database\Seeders\Exam;

use App\AramiscClass;
use App\AramiscStudent;
use App\AramiscClassSection;
use App\AramiscAssignSubject;
use Faker\Factory as Faker;
use App\AramiscExamMarksRegister;
use App\Models\StudentRecord;
use Illuminate\Database\Seeder;

class AramiscExamMarksRegistersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id)
    {
        $faker = Faker::create();

        $classSection = AramiscClassSection::where('school_id',$school_id)->where('academic_id', $academic_id)->first();
        $students = StudentRecord::where('class_id', $classSection->class_id)->where('section_id', $classSection->section_id)->where('school_id',$school_id)->where('academic_id', $academic_id)->get();
        foreach ($students as $record) {

            $class_id = $record->class_id;
            $section_id = $record->section_id;
            $subjects = AramiscAssignSubject::where('school_id',$school_id)->where('academic_id', $academic_id)->where('class_id', $class_id)->where('section_id', $section_id)->get();
            foreach ($subjects as $subject) {
                $store = new AramiscExamMarksRegister();
                $store->exam_id = 1;
                $store->student_id = $record->student_id;
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
