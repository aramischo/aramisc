<?php

namespace Database\Seeders\OnlineExam;

use App\AramiscQuestionGroup;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class AramiscQuestionGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=5)
    {
        $faker = Faker::create();
        for($i=1; $i<=5; $i++){
            $store= new AramiscQuestionGroup();
            $store->title=$faker->word;
            $store->created_at = date('Y-m-d h:i:s');
            $store->school_id = $school_id;
            $store->academic_id = $academic_id;
            $store->save();

        }
    }
}
