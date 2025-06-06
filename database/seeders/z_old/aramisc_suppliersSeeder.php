<?php

namespace Database\Seeders;

use App\AramiscSupplier;
use Illuminate\Database\Seeder;

class aramisc_suppliersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data=['AB','ABD','Amla','Sam','Gyle'];
        foreach ($data as $row) {
            for ($i = 1; $i <= 1; $i++) {
                $s= new AramiscSupplier();
                $s->company_name=$row.' '.$i;
                $s->company_address='Dhaka-bnagladesh';
                $s->contact_person_name=$row;
                $s->created_at = date('Y-m-d h:i:s');
                $s->school_id=$i;
                $s->save();
            }
        } 
    }
}
