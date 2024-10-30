<?php

namespace Database\Seeders;

use App\AramiscClass;
use App\AramiscSection;
use App\AramiscClassSection;
use Illuminate\Database\Seeder;

class sm_class_sectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $classes = AramiscClass::where('school_id', 1)->get();
        $sections = AramiscSection::where('school_id', 1)->get();
        foreach ($classes as $class) {           
            foreach ($sections as $section) {
                $s = new AramiscClassSection();
                $s->class_id = $class->id;
                $s->section_id = $section->id;
                $s->school_id = 1;              
                $s->save();
            }
        }
    }
}
