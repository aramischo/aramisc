<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\AramiscProductPurchase;
class CreateAramiscProductPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_product_purchases', function (Blueprint $table) {
            $table->increments('id');
            $table->date('purchase_date');
            $table->date('expaire_date'); 
            $table->float('price', 10, 2)->nullable(); 
            $table->float('paid_amount', 10, 2)->nullable();
            $table->float('due_amount', 10, 2)->nullable();
            $table->string('package', 10, 2)->nullable();
            $table->timestamps();

            $table->integer('user_id')->nullable()->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('staff_id')->nullable()->unsigned();
            $table->foreign('staff_id')->references('id')->on('aramisc_staffs')->onDelete('cascade');

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
            
        });
 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_product_purchases');
    }
}
