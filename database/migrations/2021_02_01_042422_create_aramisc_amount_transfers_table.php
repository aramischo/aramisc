<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAramiscAmountTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_amount_transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('amount')->nullable();
            $table->string('purpose')->nullable();
            $table->integer('from_payment_method')->nullable();
            $table->integer('from_bank_name')->nullable();
            $table->integer('to_payment_method')->nullable();
            $table->integer('to_bank_name')->nullable();
            $table->date('transfer_date')->nullable();
            $table->tinyInteger('active_status')->nullable()->default(1);
            $table->timestamps();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');   
            
            $table->integer('academic_id')->nullable()->default(1)->unsigned();
            $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_amount_transfers');
    }
}
