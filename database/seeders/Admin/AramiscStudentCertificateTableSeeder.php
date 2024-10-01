<?php

namespace Database\Seeders\Admin;

use App\AramiscStudentCertificate;
use Illuminate\Database\Seeder;

class AramiscStudentCertificateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=1)
    {
        $school_academic =[
            'school_id'=>$school_id,
            'academic_id'=>$academic_id,
        ];
        AramiscStudentCertificate::factory()->times($count)->create($school_academic);

    }
}
