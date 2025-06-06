<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\AramiscSubjectAttendance;
class CreateAramiscSubjectAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_subject_attendances', function (Blueprint $table) {
            $table->increments('id');
            $table->string('attendance_type', 10)->nullable()->comment('Present: P Late: L Absent: A Holiday: H Half Day: F');
            $table->string('notes', 500)->nullable();
            $table->date('attendance_date')->nullable();
            $table->boolean('notify')->default(false);
            $table->timestamps();

            $table->integer('class_id')->nullable()->unsigned();
            $table->foreign('class_id')->references('id')->on('aramisc_classes')->onDelete('cascade');

            $table->integer('section_id')->nullable()->unsigned();
            $table->foreign('section_id')->references('id')->on('aramisc_sections')->onDelete('cascade');

            $table->integer('subject_id')->nullable()->unsigned();
            $table->foreign('subject_id')->references('id')->on('aramisc_subjects')->onDelete('cascade');

            $table->integer('student_id')->nullable()->unsigned();
            $table->foreign('student_id')->references('id')->on('aramisc_students')->onDelete('cascade');

            $table->bigInteger('student_record_id')->nullable()->unsigned();
            $table->foreign('student_record_id')->references('id')->on('student_records')->onDelete('cascade');

            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
            
            $table->integer('academic_id')->nullable()->default(1)->unsigned();
            $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');

            $table->integer('active_status')->nullable()->default(1);
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_subject_attendances');
    }
}
