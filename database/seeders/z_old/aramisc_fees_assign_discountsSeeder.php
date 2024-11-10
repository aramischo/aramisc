<?php

namespace Database\Seeders;

use App\AramiscFeesAssignDiscount;
use App\AramiscStudent;
use Illuminate\Database\Seeder;

class aramisc_fees_assign_discountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        AramiscFeesAssignDiscount::query()->truncate();
        $students = AramiscStudent::where('class_id', 1)->get();
        foreach ($students as $student) {
            $store = new AramiscFeesAssignDiscount();
            $store->fees_discount_id = 1;
            $store->student_id = $student->id;
            $store->save();
        }
    }
}
