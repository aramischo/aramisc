<?php

namespace Database\Seeders;

use App\AramiscFeesGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
// use DB;

class aramisc_fees_groupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        AramiscFeesGroup::where('id','>', 2)->delete();
        DB::table('aramisc_fees_groups')->insert([          
            [
                'name' => 'Library Fee',
                'type' => 'System',
                'description' => 'System Automatic created this fee group',
            ],
            [
                'name' => 'Processing Fee',
                'type' => 'System',
                'description' => 'System Automatic created this fee group',
            ],
            [
                'name' => 'Tuition Fee',
                'type' => 'System',
                'description' => 'System Automatic created this fee group',
            ],
            [
                'name' => 'Others Fee',
                'type' => 'System',
                'description' => 'System Automatic created this fee group',
            ],
            [
                'name' => 'Lab Fee',
                'type' => 'System',
                'description' => 'System Automatic created this fee group',
            ],
            [
                'name' => 'Development Fee',
                'type' => 'System',
                'description' => 'System Automatic created this fee group',
            ]

        ]);

    }
}
