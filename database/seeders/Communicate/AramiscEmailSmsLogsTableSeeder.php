<?php

namespace Database\Seeders\Communicate;

use App\AramiscEmailSmsLog;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class AramiscEmailSmsLogsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count = 5)
    {

        AramiscEmailSmsLog::factory()->times($count)->create([
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ]);

    }
}
