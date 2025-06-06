<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\AramiscChartOfAccount;

class CreateAramiscChartOfAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_chart_of_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('head', 200)->nullable();
            $table->string('type', 1)->nullable()->comment('E = expense, I = income');
            $table->integer('active_status')->nullable()->default(1);
            $table->timestamps();

            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');

            $table->integer('academic_id')->nullable()->unsigned();
            $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');
        });

        $store = new AramiscChartOfAccount();
        $store->id = 1;
        $store->head = 'Fees Collection';
        $store->type = 'I';
        $store->save();



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_chart_of_accounts');
    }
}
