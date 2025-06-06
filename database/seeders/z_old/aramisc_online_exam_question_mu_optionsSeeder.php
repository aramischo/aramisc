<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\AramiscOnlineExamQuestionMuOption;

class aramisc_online_exam_question_mu_optionsSeeder extends Seeder
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
            $store= new AramiscOnlineExamQuestionMuOption();
            $store->online_exam_question_id=$i;
            $store->title=$faker->realText($maxNbChars = 30, $indexSize = 1);
            $store->status=0;
            $store->created_by=1;
            $store->created_at = date('Y-m-d h:i:s');
            $store->save();
        }
    }
}
