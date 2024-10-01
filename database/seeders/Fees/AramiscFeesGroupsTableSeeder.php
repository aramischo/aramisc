<?php

namespace Database\Seeders\Fees;

use App\AramiscFeesType;
use App\AramiscFeesGroup;
use App\AramiscFeesMaster;
use Illuminate\Database\Seeder;

class AramiscFeesGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=5)
    {
     
        $school_academic = ['school_id'=>$school_id, 'academic_id'=>$academic_id];
       
        AramiscFeesGroup::factory()->times($count)->create($school_academic)->each(function ($feesGroup) use ($school_academic) {
            AramiscFeesType::factory()->times(5)->create(array_merge([
                'fees_group_id' => $feesGroup->id,
            ], $school_academic))->each(function ($feesTypes) use ($school_academic) {
                AramiscFeesMaster::factory()->times(1)->create(array_merge([
                    'fees_group_id' => $feesTypes->fees_group_id,
                    'fees_type_id' => $feesTypes->id,
                ], $school_academic));
            });
        });
    }
}
