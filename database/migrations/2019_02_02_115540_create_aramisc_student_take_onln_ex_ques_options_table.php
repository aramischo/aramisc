<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscStudentTakeOnlnExQuesOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_student_take_onln_ex_ques_options', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->tinyInteger('status')->nullable()->comment('0 unchecked 1 checked');
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

            $table->integer('take_online_exam_question_id')->nullable()->unsigned();
            $table->foreign('take_online_exam_question_id','t_on_ex_q_id')->references('id')->on('aramisc_student_take_online_exam_questions')->onDelete('cascade');

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
        Schema::dropIfExists('aramisc_student_take_onln_ex_ques_options');
    }
}
