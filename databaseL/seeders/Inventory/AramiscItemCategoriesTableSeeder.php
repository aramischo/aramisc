<?php

namespace Database\Seeders\Inventory;

use App\AramiscItem;
use App\AramiscItemCategory;
use Illuminate\Database\Seeder;

class AramiscItemCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=5)
    {
        //
        $school_academic=[
            'school_id'=>$school_id,
            'academic_id'=>$academic_id,
        ];
        AramiscItemCategory::factory()->times($count)->create($school_academic)->each(function ($itemCategory) use($school_academic, $count){
            AramiscItem::factory()->times($count)->create(array_merge([
                'item_category_id' =>$itemCategory->id,
            ],$school_academic));
        });
    }
}
