<?php

namespace Database\Seeders\Library;

use App\AramiscBook;
use App\AramiscBookCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AramiscBookCategoriesTableSeeder extends Seeder
{
    public function run($school_id = 1, $count = 16){

        AramiscBookCategory::factory()->times($count)->create([
            'school_id' => $school_id,
        ])->each(function ($book_category){
            AramiscBook::factory()->times(11)->create([
               'school_id' => $book_category->school_id,
            ]);
        });
    }
}