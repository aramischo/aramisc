<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\AramiscStudentGroup;

class sm_student_groupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        for($i=1; $i<=5; $i++){
            $store= new AramiscStudentGroup();
            $store->group=$faker->word;
            $store->created_at = date('Y-m-d h:i:s');
            $store->save();

        }
    }
}
