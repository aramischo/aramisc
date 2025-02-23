<?php

namespace Database\Seeders;

use App\AramiscRoomType;
use Illuminate\Database\Seeder;

class aramisc_room_typesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $data =[
            ['Single','A room assigned to one person. May have one or more beds.'],
            ['Double','A room assigned to two people. May have one or more beds.'],
            ['Triple','A room assigned to three people. May have two or more beds'],
            ['Quad','A room assigned to four people. May have two or more beds.'],
            ['Queen','A room with a queen-sized bed. May be occupied by one or more people'],
            ['King','A room with a king-sized bed. May be occupied by one or more people.']
        ];

        foreach ($data as $row) {
            $store = new AramiscRoomType();
            $store->type =$row[0];
            $store->description =$row[1];
            $store->created_at = date('Y-m-d h:i:s');
            $store->save();
        }
    }
}
