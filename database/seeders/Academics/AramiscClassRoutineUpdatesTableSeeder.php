<?php

namespace Database\Seeders\Academics;

use App\AramiscWeekend;
use App\AramiscAssignSubject;
use App\AramiscClassRoutineUpdate;
use Illuminate\Database\Seeder;

class AramiscClassRoutineUpdatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=1)
    {
        $school_academic = [
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ];
        $classSectionSubjects=AramiscAssignSubject::where('school_id',$school_id)
        ->where('academic_id',$academic_id)
        ->get();
        $weekends = AramiscWeekend::where('school_id', $school_id)->get();
        foreach ($weekends as $day){
            foreach($classSectionSubjects as  $classSectionSubject){
                AramiscClassRoutineUpdate::factory()->times($count)->create(array_merge([
                    'day' => $day->id,
                    'class_id' => $classSectionSubject->class_id,
                    'section_id' => $classSectionSubject->section_id,
                    'subject_id' => $classSectionSubject->subject_id,
                ], $school_academic));
            }
        }

    }
}
