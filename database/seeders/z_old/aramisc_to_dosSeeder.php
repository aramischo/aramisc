<?php

namespace Database\Seeders;

use App\AramiscToDo;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class aramisc_to_dosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        for ($i = 1; $i <= 5; $i++) {
            $store = new AramiscToDo();
            $store->todo_title = $faker->realText($maxNbChars = 30, $indexSize = 1);
            $store->date = $faker->dateTime()->format('Y-m-d');
            $store->created_by = 1;
            $store->created_at = date('Y-m-d h:i:s');
            $store->save();
        }
    }
}
