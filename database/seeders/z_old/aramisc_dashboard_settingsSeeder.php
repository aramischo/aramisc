<?php

namespace Database\Seeders;

use App\AramiscDashboardSetting;
use Illuminate\Database\Seeder;

class aramisc_dashboard_settingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AramiscDashboardSetting::query()->truncate();

    }
}
