<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscStaffAttendencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_staff_attendences', function (Blueprint $table) {
            $table->increments('id');
            $table->string('attendence_type', 10)->nullable()->comment('Present: P Late: L Absent: A Holiday: H Half Day: F');
            $table->string('notes', 500)->nullable();
            $table->date('attendence_date')->nullable();
            $table->timestamps();

            $table->integer('staff_id')->nullable()->unsigned();
            $table->foreign('staff_id')->references('id')->on('aramisc_staffs')->onDelete('cascade');

            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
            
            $table->integer('academic_id')->nullable()->unsigned();
            $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');
        });

        //   Schema::table('aramisc_staff_attendences', function($table) {
        //      $table->foreign('staff_id')->references('id')->on('aramisc_staffs');

        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_staff_attendences');
    }
}
