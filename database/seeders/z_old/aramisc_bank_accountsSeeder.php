<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\AramiscBankAccount;
use Faker\Factory as Faker;

class aramisc_bank_accountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AramiscBankAccount::factory()->times(10)->create();
    }
}
