<?php

namespace Database\Seeders;

use App\AramiscPostalReceive;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class aramisc_postal_receivesSeeder extends Seeder
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
            $store = new AramiscPostalReceive();

            $store->from_title = $faker->name;
            $store->to_title = $faker->name;
            $store->reference_no = $faker->ean8;
            $store->address = $faker->address;
            $store->date = $faker->dateTime()->format('Y-m-d');
            $store->note = $faker->realText($maxNbChars = 100, $indexSize = 1);
            $store->file = '';
            $store->created_by=1;
            $store->created_at = date('Y-m-d h:i:s');
            $store->save();
        }
    }
}
