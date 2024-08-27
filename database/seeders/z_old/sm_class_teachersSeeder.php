<?php

namespace Database\Seeders;

use App\AramiscClassTeacher;
use App\AramiscStaff;
use Illuminate\Database\Seeder;

class sm_class_teachersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $staff_id = AramiscStaff::where('role_id', 4)->first()->id ?? null;
        $store = new AramiscClassTeacher();
        $store->assign_class_teacher_id = 1;
        $store->teacher_id = $staff_id;
        $store->created_at = date('Y-m-d h:i:s');
        $store->save();

    }
}
