<?php

namespace Database\Seeders;

use App\AramiscStudent;
use App\AramiscStudentAttendance;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class aramisc_student_attendancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        AramiscStudentAttendance::query()->truncate();
        $studentList = AramiscStudent::where('class_id', 1)->get();
        foreach ($studentList as $student) {
            // $m = date('m');
            for ($m = 1; $m <= 2; $m++) {
                for ($d = 1; $d <= 30; $d++) {
                    if ($d <= 9) {
                        $d = '0' . $d;
                    }
                    $str = date('Y') . '-' . $m . '-' . $d;
                    if ($d % 3 == 0) {
                        $status = 'A';
                    } elseif ($d % 3 == 1) {
                        $status = 'L';
                    } else {
                        $status = 'P';
                    }
                    if ($m == 2 && $d == 28) {
                        break;
                    }

                    $sa                  = new AramiscStudentAttendance();
                    $sa->student_id      = $student->id;
                    $sa->attendance_type = $status;
                    $sa->notes           = 'Sample Attendance for ' . $str;
                    $sa->attendance_date = $str;
                    $sa->created_at = date('Y-m-d h:i:s');
                    $sa->save();
                }
            }
        }
    }
}
