<?php

namespace Database\Seeders;

use App\AramiscBookCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
// use DB;

class aramisc_book_categoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        AramiscBookCategory::query()->truncate();
        $book_categories=['Action and adventure','Alternate history','Anthology','Chick lit','Kids','Comic book','Coming-of-age','Crime','Drama',
            'Fairytale','Fantasy','Graphic novel','Historical fiction','Horror', 'Mystery','Paranormal romance'];
        foreach ($book_categories as $c) {
            DB::table('aramisc_book_categories')->insert([
                [
                    'category_name' => $c
                ]
            ]);
        }
    }
}
