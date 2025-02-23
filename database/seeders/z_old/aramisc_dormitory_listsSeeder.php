<?php

namespace Database\Seeders;

use App\AramiscDormitoryList;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class aramisc_dormitory_listsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        //    AramiscDormitoryList::query()->truncate();

        $dormitory = [
            'Sir Isaac Newton Hostel',
            'Louis Pasteur Hostel',
            'Galileo Hostel',
            'Marie Curie Hostel',
            'Albert Einstein Hostel',
            'Charles Darwin Hostel',
            'Nikola Tesla Hostel'
        ];


        foreach ($dormitory as $data) {
            DB::table('aramisc_dormitory_lists')->insert([
                [
                    'dormitory_name' => $data,
                    'type' => 'B',
                    'address' => '25/13, Sukrabad Rd, Tallahbag, Dhaka 1215',
                    'intake' => 120,
                    'created_at' => date('Y-m-d h:i:s'),
                    'description' => 'Hostels provide lower-priced, sociable accommodation where guests can rent a bed, usually a bunk bed, in a dormitory and share a bathroom, lounge and sometimes a kitchen.',
                ]
            ]);
        }
    }
}
