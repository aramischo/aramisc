<?php

namespace Database\Seeders\HumanResources;

use App\User;
use App\SmRoute;
use App\SmStaff;
use App\SmStaffAttendence;
use App\Models\SmExpertTeacher;
use Illuminate\Database\Seeder;

class StaffsTableSeeder  extends Seeder
{

    public function run($school_id , $count = 10){
       

        User::factory()->times($count)->create([
            'school_id' => $school_id,
        ])->each( function ($userStaff) use ($school_id) {
            SmStaff::factory()->times(1)->create([
                'user_id' => $userStaff->id,
                'email' => $userStaff->email,
                'first_name' => $userStaff->first_name,
                'last_name' => $userStaff->last_name,
                'full_name' => $userStaff->full_name,
                'school_id' => $school_id,
                'role_id' => rand(5,9),
            ])->each(function($s) use($school_id){
                $s->staff_no = $s->id;
                $s->mobile = '+8801234567'.$s->id;
                $s->save();
                
                $i = 0;
                SmExpertTeacher::factory()->times(1)->create([
                    'staff_id' => $s->id,
                    'created_by' => 1,
                    'updated_by' => 1,
                    'position' => $i++,
                ]);

                $aramiscAttendance_type = ['P', 'L', 'A', 'F'];
                foreach(lastOneMonthDates() as $date){
                    shuffle($aramiscAttendance_type);
                    $aramiscAttendanceStaff = new SmStaffAttendence();
                    $aramiscAttendanceStaff->staff_id = $s->id;
                    $aramiscAttendanceStaff->school_id = $school_id;
                    $aramiscAttendanceStaff->attendence_type = $aramiscAttendance_type[0];
                    $aramiscAttendanceStaff->notes = $aramiscAttendanceStaff->aramiscAttendance_type == "P" ? "Good" : "Bad";
                    $aramiscAttendanceStaff->attendence_date = $date;
                    $aramiscAttendanceStaff->save();
                }
            });
        });

        // User::factory()->times($count)->create([
        //     'school_id' => $school_id,
        // ])->each( function ($userStaff) use ($school_id) {
        //     SmStaff::factory()->times(1)->create([
        //         'user_id' => $userStaff->id,
        //         'email' => $userStaff->email,
        //         'first_name' => $userStaff->first_name,
        //         'last_name' => $userStaff->last_name,
        //         'full_name' => $userStaff->full_name,
        //         'school_id' => $school_id,
        //         'role_id' =>4,
        //     ])->each(function($s){
        //         $s->staff_no = $s->id;
        //         $s->save();
        //     });
        // });
    }

}