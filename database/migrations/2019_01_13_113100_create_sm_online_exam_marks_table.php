<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscOnlineExamMarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sm_online_exam_marks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('marks')->nullable();
            $table->integer('abs')->default(0);
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

            $table->integer('student_id')->nullable()->unsigned();
            $table->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');

            $table->integer('subject_id')->nullable()->unsigned();
            $table->foreign('subject_id')->references('id')->on('sm_subjects')->onDelete('cascade');

            $table->integer('exam_id')->nullable()->unsigned();
            $table->foreign('exam_id')->references('id')->on('sm_exams')->onDelete('cascade');

            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            
            $table->integer('academic_id')->nullable()->default(1)->unsigned();
            $table->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sm_online_exam_marks');
    }
}
