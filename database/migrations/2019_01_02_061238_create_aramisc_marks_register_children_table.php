<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscMarksRegisterChildrenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_marks_register_children', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('marks')->nullable();
            $table->integer('abs')->default(0)->comment('1 for absent, 0 for present');
            $table->float('gpa_point')->nullable();
            $table->string('gpa_grade',55)->nullable();
 
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

            $table->integer('marks_register_id')->nullable()->unsigned();
            $table->foreign('marks_register_id')->references('id')->on('aramisc_marks_registers')->onDelete('cascade');

            $table->integer('subject_id')->nullable()->unsigned();
            $table->foreign('subject_id')->references('id')->on('aramisc_subjects')->onDelete('cascade');

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
        Schema::dropIfExists('aramisc_marks_register_children');
    }
}
