<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscOnlineExamQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_online_exam_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 1)->nullable();
            $table->integer('mark')->nullable();
            $table->text('title')->nullable();
            $table->string('trueFalse', 1)->nullable()->comment('F = false, T = true ');
            $table->text('suitable_words')->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();
            
            $table->integer('online_exam_id')->nullable()->unsigned();
            $table->foreign('online_exam_id')->references('id')->on('aramisc_online_exams')->onDelete('cascade');

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
        Schema::dropIfExists('aramisc_online_exam_questions');
    }
}
