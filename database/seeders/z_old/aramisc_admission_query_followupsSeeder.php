<?php

namespace Database\Seeders;

use App\AramiscAdmissionQueryFollowup;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class aramisc_admission_query_followupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // AramiscAdmissionQueryFollowup::query()->truncate();
        $faker = Faker::create();
        for ($i = 1; $i <= 3; $i++) {
            $s = new AramiscAdmissionQueryFollowup();
            $s->admission_query_id = $i;
            $s->response = $faker->sentence($nbWords = 3, $variableNbWords = true);
            $s->note = $faker->sentence($nbWords = 4, $variableNbWords = true);
            $s->date = date('Y-m-d');
            $s->created_at = date('Y-m-d h:i:s');
            $s->save();
        }
    }
}
