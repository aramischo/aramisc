<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscStudentAttendanceImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_student_attendance_imports', function (Blueprint $table) {
            $table->increments('id');
            $table->date('attendance_date')->nullable();
            $table->string("in_time", 50)->nullable();
            $table->string("out_time", 50)->nullable();
            $table->string('attendance_type',10)->nullable()->comment('Present: P Late: L Absent: A Holiday: H Half Day: F');
            $table->string('notes',500)->nullable();
            $table->timestamps();

            $table->integer('student_id')->nullable()->unsigned();
            $table->foreign('student_id')->references('id')->on('aramisc_students')->onDelete('cascade');

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
        Schema::dropIfExists('aramisc_student_attendance_imports');
    }
}
