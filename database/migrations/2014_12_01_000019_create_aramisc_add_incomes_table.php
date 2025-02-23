<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\AramiscAddIncome;

class CreateAramiscAddIncomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_add_incomes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->nullable();
            $table->date('date')->nullable();
            $table->float('amount', 10, 2)->nullable();
            $table->string('file')->nullable();
            $table->text('description')->nullable();
            $table->integer('item_sell_id')->nullable();
            $table->integer('fees_collection_id')->nullable();
            $table->integer('inventory_id')->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();



            $table->integer('income_head_id')->nullable()->unsigned();

            $table->integer('account_id')->nullable()->unsigned();
            $table->foreign('account_id')->references('id')->on('aramisc_bank_accounts')->onDelete('cascade');

            $table->integer('payment_method_id')->nullable()->unsigned();
            $table->foreign('payment_method_id')->references('id')->on('aramisc_payment_methhods')->onDelete('cascade');

            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');

            $table->integer('academic_id')->nullable()->unsigned();
            $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');
            $table->integer('installment_payment_id')->nullable()->unsigned();
        });



        // $store = new AramiscAddIncome();
        // $store->name                =           'Donation for Boys football match';
        // $store->income_head_id     =           1;
        // $store->payment_method_id   =           1;
        // $store->date                =           '2019-05-05';
        // $store->amount              =           1200;
        // $store->save();



        // $store = new AramiscAddIncome();
        // $store->name                =           'Product Sales';
        // $store->income_head_id     =           2;
        // $store->payment_method_id   =           1;
        // $store->date                =           '2019-05-05';
        // $store->amount              =           15000;
        // $store->save();


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_add_incomes');
    }
}
