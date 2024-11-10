<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscInventoryPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_inventory_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_receive_sell_id')->nullable()->unsigned();
            $table->date('payment_date')->nullable();
            $table->float('amount', 10, 2)->nullable();
            $table->string('reference_no', 50)->nullable();
            $table->string('payment_type')->length(11)->nullable()->comment('R for receive S for sell');
            $table->integer('payment_method')->nullable()->unsigned();
            $table->string('notes')->length(500)->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

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
        Schema::dropIfExists('aramisc_inventory_payments');
    }
}
