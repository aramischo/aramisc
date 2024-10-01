<?php

namespace Database\Seeders\Accounts;

use App\AramiscAddExpense;
use App\AramiscAddIncome;
use App\AramiscExpenseHead;
use App\AramiscIncomeHead;
use Illuminate\Database\Seeder;

class AramiscIncomeHeadsTableSeeder extends Seeder
{

    public function run($school_id = 1, $count = 10){
        AramiscIncomeHead::factory()->times($count)->create([
            'school_id' => $school_id
        ])->each(function ($income_head){
            AramiscAddIncome::factory()->times(10)->create([
                'school_id' => $income_head->school_id,
                'income_head_id' => $income_head->id,
            ]);
        });
    }

}