<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StudyMaterialHomework extends Migration
{
    public function up()
    {
        Schema::table('aramisc_homeworks', function (Blueprint $table) {
            if (!Schema::hasColumn('aramisc_homeworks', 'course_id',)) {
                $table->unsignedBigInteger('course_id')->nullable();
            }
            if (!Schema::hasColumn('aramisc_homeworks', 'lesson_id',)) {
                $table->unsignedBigInteger('lesson_id')->nullable();
            }
            if (!Schema::hasColumn('aramisc_homeworks', 'chapter_id',)) {
                $table->unsignedBigInteger('chapter_id')->nullable();
            }
        });


        Schema::table('aramisc_teacher_upload_contents', function (Blueprint $table) {
            if (!Schema::hasColumn('aramisc_teacher_upload_contents', 'course_id')) {
                $table->unsignedBigInteger('course_id')->nullable();
            }
            if (!Schema::hasColumn('aramisc_teacher_upload_contents', 'chapter_id')) {
                $table->unsignedBigInteger('chapter_id')->nullable();
            }
            if (!Schema::hasColumn('aramisc_teacher_upload_contents', 'lesson_id')) {
                $table->unsignedBigInteger('lesson_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
