<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscOnlineExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_online_exams', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->date('date')->nullable()->nullable();
            $table->string("start_time", 200)->nullable();
            $table->string("end_time", 200)->nullable();
            $table->string('end_date_time')->nullable();
            $table->integer("percentage")->nullable();
            $table->text("instruction")->nullable();
            $table->tinyInteger("status")->nullable()->comment('0 = Pending 1 Published');
            $table->tinyInteger("is_taken")->default(0)->nullable();
            $table->tinyInteger("is_closed")->default(0)->nullable();
            $table->tinyInteger("is_waiting")->default(0)->nullable();
            $table->tinyInteger("is_running")->default(0)->nullable();
            $table->tinyInteger("auto_mark")->default(0)->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

            $table->integer('class_id')->nullable()->unsigned();
            $table->foreign('class_id')->references('id')->on('aramisc_classes')->onDelete('cascade');

            $table->integer('section_id')->nullable()->unsigned();
            $table->foreign('section_id')->references('id')->on('aramisc_sections')->onDelete('cascade');

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
        Schema::dropIfExists('aramisc_online_exams');
    }
}