<?php

namespace Database\Seeders;

use App\AramiscExpenseHead;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class aramisc_expense_headsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AramiscExpenseHead::factory()->times(10)->create();        
    }
}
