<?php

namespace Database\Seeders\Accounts;

use App\AramiscAddExpense;
use App\AramiscExpenseHead;
use Illuminate\Database\Seeder;

class AramiscExpenseHeadsTableSeeder extends Seeder
{

    public function run($school_id = 1, $count = 10){
        AramiscExpenseHead::factory()->times($count)->create([
            'school_id' => $school_id
        ])->each(function ($expense_head){
            AramiscAddExpense::factory()->times(10)->create([
                'school_id' => $expense_head->school_id,
                'expense_head_id' => $expense_head->id,
            ]);
        });
    }

}