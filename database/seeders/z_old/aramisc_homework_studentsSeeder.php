<?php

namespace Database\Seeders;

use App\AramiscHomework;
use App\AramiscHomeworkStudent;
use App\AramiscStudent;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class aramisc_homework_studentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        //        AramiscHomeworkStudent::query()->truncate();
        $faker = Faker::create();

        $students = AramiscStudent::where('class_id', 1)->where('school_id', 1)->get();
        foreach ($students as $student) {
            $homeworks = AramiscHomework::where('class_id', $student->class_id)->where('school_id', 1)->get();
            foreach ($homeworks as $homework) {
                $s = new AramiscHomeworkStudent();
                $s->student_id = $student->id;
                $s->homework_id = $homework->id;
                $s->marks = rand(5, 10);
                $s->teacher_comments = $faker->text(100);
                $s->complete_status = 'C';
                $s->created_at = date('Y-m-d h:i:s');
                $s->save();
            }
        }
    }
}
