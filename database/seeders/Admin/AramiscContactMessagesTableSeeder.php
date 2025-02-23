<?php

namespace Database\Seeders\Admin;

use App\AramiscComplaint;
use App\AramiscContactMessage;
use App\AramiscSetupAdmin;
use Database\Factories\AramiscSetupAdminFactory;
use Illuminate\Database\Seeder;

class AramiscContactMessagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $count = 5)
    {
       AramiscContactMessage::factory()->times($count)->create([
           'school_id' => $school_id
       ]);


    }
}
