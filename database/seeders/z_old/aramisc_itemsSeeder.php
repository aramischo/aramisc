<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\AramiscItem;

class aramisc_itemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=1; $i<=5; $i++){
            $s = new AramiscItem();
            $s->item_name = 'Item name '.$i;
            $s->item_category_id =$i;
            $s->total_in_stock = 23*$i;
            $s->save();
        }
    }
}
