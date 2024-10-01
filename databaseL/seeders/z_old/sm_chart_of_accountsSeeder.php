<?php

namespace Database\Seeders\Accounts;

use Illuminate\Database\Seeder;
use App\AramiscChartOfAccount;
class AramiscChartOfAccountsTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // AramiscChartOfAccount::query()->truncate();
        $store = new AramiscChartOfAccount();
        $store->head = 'Donation';
        $store->type = 'I';
        $store->save();

        $store = new AramiscChartOfAccount();
        $store->head = 'Scholarship';
        $store->type = 'E';
        $store->save();

        $store = new AramiscChartOfAccount();
        $store->head = 'Product Sales';
        $store->type = 'I';
        $store->save();

        $store = new AramiscChartOfAccount();
        $store->head = 'Utility Bills';
        $store->type = 'E';
        $store->save();
    }
}
