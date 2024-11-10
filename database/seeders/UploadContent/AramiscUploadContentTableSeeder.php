<?php

namespace Database\Seeders\UploadContent;

use App\AramiscClassSection;
use App\AramiscTeacherUploadContent;
use Illuminate\Database\Seeder;

class AramiscUploadContentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=5)
    {
        $classSection = AramiscClassSection::where('school_id', $school_id)->where('academic_id', $academic_id)->first();
        AramiscTeacherUploadContent::factory()->times($count)->create(array_merge([
            'class' => $classSection->class_id,
            'section' => $classSection->section_id,
            'school_id'=>$school_id,
            'academic_id'=>$academic_id,
        ]));
    }
}
