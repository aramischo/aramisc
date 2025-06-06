<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class aramisc_student_categoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('aramisc_student_categories')->insert([

            [
                'category_name'=> 'Normal', 
            ],     
            [
                'category_name'=> 'Obsessive Compulsive Disorder', 
            ],     
            [
                'category_name'=> 'Attention Deficit Hyperactivity Disorder (ADHD)', 
            ],     
            [
                'category_name'=> 'Oppositional Defiant Disorder (ODD)', 
            ], 
            [
                'category_name'=> 'Anxiety Disorder', 
            ], 
            [
                'category_name'=> 'Conduct Disorders', 
            ]
   
           ]);
    }
}
