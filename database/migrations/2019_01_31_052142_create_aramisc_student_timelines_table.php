<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscStudentTimelinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_student_timelines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('staff_student_id');
            $table->string('title')->nullable();
            $table->date('date')->nullable();
            $table->text('description')->nullable();
            $table->string('file')->nullable();
            $table->string('type')->nullable()->comment('stu=student,stf=staff');
            $table->integer('visible_to_student')->default(0)->comment('0 = no, 1 = yes');
            $table->integer('active_status')->default(1);
            $table->timestamps();


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
        Schema::dropIfExists('aramisc_student_timelines');
    }
}
