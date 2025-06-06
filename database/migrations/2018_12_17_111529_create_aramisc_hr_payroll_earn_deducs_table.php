<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscHrPayrollEarnDeducsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_hr_payroll_earn_deducs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type_name')->nullable();
            $table->float('amount', 10, 2)->nullable();
            $table->string('earn_dedc_type')->length(5)->nullable()->comment('e for earnings and d for deductions');
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();



            $table->integer('payroll_generate_id')->nullable()->unsigned();
            $table->foreign('payroll_generate_id')->references('id')->on('aramisc_hr_payroll_generates')->onDelete('cascade');

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
        Schema::dropIfExists('aramisc_hr_payroll_earn_deducs');
    }
}
