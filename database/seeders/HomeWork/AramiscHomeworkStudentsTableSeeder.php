<?php

namespace Database\Seeders\HomeWork;

use App\AramiscClass;
use App\AramiscHomework;
use App\AramiscHomeworkStudent;
use Faker\Factory as Faker;
use App\Models\StudentRecord;
use Illuminate\Database\Seeder;

class AramiscHomeworkStudentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=5)
    {
        $faker = Faker::create();
        $class = AramiscClass::where('school_id', $school_id)->where('academic_id', $academic_id)->value('id');
        $students = StudentRecord::where('class_id', 1)->where('school_id', $school_id)->get();
        foreach ($students as $record) {
            $homeworks = AramiscHomework::where('class_id', $record->class_id)->where('school_id', 1)->get();
            foreach ($homeworks as $homework) {
                $s = new AramiscHomeworkStudent();
                $s->student_id = $record->student_id;
                // $s->student_record_id = $record->id;
                $s->homework_id = $homework->id;
                $s->marks = rand(5, 10);
                $s->teacher_comments = $faker->text(100);
                $s->complete_status = 'C';
                $s->created_at = date('Y-m-d h:i:s');
                $s->school_id = $school_id;
                $s->academic_id = $academic_id;
                $s->save();
            }
        }
    }
}
