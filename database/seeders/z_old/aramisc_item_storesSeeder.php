<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\AramiscItemStore;
class aramisc_item_storesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=1; $i<=5; $i++){
            $s = new AramiscItemStore();
            $s->store_name = 'Store '.$i;
            $s->store_no = 100*$i;
            $s->description = 'Store '.$i;
            $s->save();
        }
    }
}
