<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscExamMarksRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_exam_marks_registers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('obtained_marks', 200)->nullable();
            $table->date('exam_date')->nullable();
            $table->string('comments', 500)->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

            $table->integer('exam_id')->unsigned();
            $table->foreign('exam_id')->references('id')->on('aramisc_exams')->onDelete('cascade');

            $table->integer('student_id')->nullable()->unsigned();
            $table->foreign('student_id')->references('id')->on('aramisc_students')->onDelete('cascade');

            $table->integer('subject_id')->nullable()->unsigned();
            $table->foreign('subject_id')->references('id')->on('aramisc_subjects')->onDelete('cascade');


            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
            
            $table->integer('academic_id')->nullable()->default(1)->unsigned();
            $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');
        });

        //  Schema::table('aramisc_exam_marks_registers', function($table) {
        //     $table->foreign('exam_id')->references('id')->on('aramisc_exams');
        //     $table->foreign('student_id')->references('id')->on('aramisc_students');
        //     $table->foreign('subject_id')->references('id')->on('aramisc_subjects');

        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_exam_marks_registers');
    }
}
