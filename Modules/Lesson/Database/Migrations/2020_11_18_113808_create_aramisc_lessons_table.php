<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAramiscLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_lessons', function (Blueprint $table) {
            $table->id();
            $table->string('lesson_title')->nullable();
            $table->integer('active_status')->default(1);

            $table->integer('class_id')->nullable()->unsigned();
            $table->foreign('class_id')->references('id')->on('aramisc_classes')->onDelete('cascade');

            $table->integer('section_id')->nullable()->unsigned();
            $table->foreign('section_id')->references('id')->on('aramisc_sections')->onDelete('cascade');

            $table->integer('subject_id')->nullable()->unsigned();
            $table->foreign('subject_id')->references('id')->on('aramisc_subjects')->onDelete('cascade');

            
            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');  
            
            $table->integer('academic_id')->nullable()->default(1)->unsigned();
            $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');

            $table->integer('user_id')->nullable();
            $table->timestamps();
        });
         
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_lessons');
    }
}
