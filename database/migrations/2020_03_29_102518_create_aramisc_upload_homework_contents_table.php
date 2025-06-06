<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscUploadHomeworkContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_upload_homework_contents', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('student_id')->nullable()->default(1)->unsigned();
            $table->foreign('student_id')->references('id')->on('aramisc_students')->onDelete('cascade');

            $table->integer('homework_id')->nullable()->default(1)->unsigned();
            $table->foreign('homework_id')->references('id')->on('aramisc_homeworks')->onDelete('cascade');

            $table->text('description')->nullable();
            $table->text('file')->nullable();

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
        Schema::dropIfExists('aramisc_upload_homework_contents');
    }
}
