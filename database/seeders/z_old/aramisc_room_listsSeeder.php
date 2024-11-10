<?php

namespace Database\Seeders;

use App\AramiscDormitoryList;
use App\AramiscRoomList;
use App\AramiscRoomType;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class aramisc_room_listsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $faker = Faker::create();
        $dormitories = AramiscDormitoryList::take(3)->get(); //7
        $rooms = AramiscRoomType::take(3)->get(); //6

        foreach ($dormitories as $dormitory) {
            foreach ($rooms as $room) {
                $store = new AramiscRoomList();
                $store->name = $faker->text(10);
                $store->dormitory_id = $dormitory->id;
                $store->room_type_id = $room->id;
                $store->number_of_bed = rand(40,100);
                $store->cost_per_bed = rand(5000,7000);
                $store->description = $faker->text(200);
                $store->created_at = date('Y-m-d h:i:s');
                $store->save();
            }
        }

    }
}
