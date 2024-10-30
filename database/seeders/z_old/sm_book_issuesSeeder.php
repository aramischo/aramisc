<?php

namespace Database\Seeders;

use App\AramiscBookIssue;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\AramiscStudent;
use App\AramiscBook;

class sm_book_issuesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // AramiscBookIssue::query()->truncate();
        $faker = Faker::create();
        $studentList = AramiscStudent::where('class_id', 1)->get();
        foreach ($studentList as $student) {
            $store = new AramiscBookIssue();
            $store->member_id = $student->id;
            $store->book_id = $faker->numberBetween(1, 11);
            $store->quantity = rand(1, 5);
            $store->given_date = $faker->dateTime()->format('Y-m-d');
            $store->due_date = $faker->dateTime()->format('Y-m-d');
            $store->issue_status = "I";
            $store->note = $faker->sentence($nbWords = 3, $variableNbWords = true);

            $store->save();
        }
    }
}
