<?php

use App\AramiscModuleManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveXenditPaymentFromDefaultModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $xenditPayment = AramiscModuleManager::where('name', 'XenditPayment')->first();
        if($xenditPayment){
            $xenditPayment->is_default = 0;
            $xenditPayment->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $xenditPayment = AramiscModuleManager::where('name', 'XenditPayment')->first();
        if($xenditPayment){
            $xenditPayment->is_default = 1;
            $xenditPayment->save();
        }
    }
}
