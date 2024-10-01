<?php

namespace Database\Seeders\Accounts;

use App\AramiscBankAccount;
use Illuminate\Database\Seeder;

class AramiscBankAccountsTableSeeder extends Seeder
{
    public function run($school_id = 1, $count = 10){
        AramiscBankAccount::factory()->times($count)->create([
            'school_id' => $school_id
        ]);
    }
}