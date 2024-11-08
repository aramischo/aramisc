<?php

namespace Database\Seeders\Communicate;

use App\AramiscSendMessage;
use Illuminate\Database\Seeder;

class AramiscSendMessageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=10)
    {
        AramiscSendMessage::factory()->times($count)->create([
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ]);
    }
}
