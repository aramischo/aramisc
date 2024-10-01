<?php

namespace Database\Seeders\Admin;

use App\AramiscComplaint;
use App\AramiscSetupAdmin;
use Database\Factories\AramiscSetupAdminFactory;
use Illuminate\Database\Seeder;

class AramiscComplaintsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count = 5)
    {
        AramiscSetupAdmin::factory()->times($count)->create([
            'type' => 2,
            'school_id' => $school_id,
            'academic_id' => $academic_id
        ])->each(function ($complaint_type) use ($count){
            AramiscComplaint::factory()->times($count)->create([
                'school_id' => $complaint_type->school_id,
                'academic_id' => $complaint_type->academic_id
            ]);
        });


    }
}
