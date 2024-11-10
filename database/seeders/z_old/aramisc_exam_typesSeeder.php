<?php

namespace Database\Seeders;

use App\AramiscExamType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class aramisc_exam_typesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //        AramiscExamType::query()->truncate();
        DB::table('aramisc_exam_types')->insert([

            [
                'school_id' => 1,
                'active_status' => 1,
                'title' => 'First Term',
                'created_at' => date('Y-m-d h:i:s')
            ],
            [
                'school_id' => 1,
                'active_status' => 1,
                'title' => 'Second Term',
                'created_at' => date('Y-m-d h:i:s')
            ],
            [
                'school_id' => 1,
                'active_status' => 1,
                'title' => 'Third Term',
                'created_at' => date('Y-m-d h:i:s')
            ],

        ]);
    }
}
