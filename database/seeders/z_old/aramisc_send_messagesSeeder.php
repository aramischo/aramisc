<?php

namespace Database\Seeders;

use App\AramiscSendMessage;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class aramisc_send_messagesSeeder extends Seeder
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
            $store = new AramiscSendMessage();
            $store->message_title = $faker->realText($maxNbChars = 30, $indexSize = 2);
            $store->message_des = $faker->realText($maxNbChars = 100, $indexSize = 2);
            $store->notice_date = $faker->dateTime()->format('Y-m-d');
            $store->publish_on = $faker->dateTime()->format('Y-m-d');
            $store->message_to = "2,3,9";
            $store->created_at = date('Y-m-d h:i:s');
            $store->save();
        }
    }
}
