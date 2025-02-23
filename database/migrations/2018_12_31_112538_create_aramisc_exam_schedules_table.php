<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscExamSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_exam_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date')->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

            $table->integer('exam_period_id')->nullable()->unsigned();
            $table->foreign('exam_period_id')->references('id')->on('aramisc_class_times')->onDelete('cascade');

            $table->integer('room_id')->nullable()->unsigned();
            // $table->foreign('room_id')->references('id')->on('aramisc_class_rooms')->onDelete('cascade');

            $table->integer('subject_id')->nullable()->unsigned();
            $table->foreign('subject_id')->references('id')->on('aramisc_subjects')->onDelete('cascade');

            $table->integer('exam_term_id')->nullable()->unsigned();
            $table->foreign('exam_term_id')->references('id')->on('aramisc_exam_types')->onDelete('cascade');

            $table->integer('exam_id')->nullable()->unsigned();
            $table->foreign('exam_id')->references('id')->on('aramisc_exams')->onDelete('cascade');

            $table->integer('class_id')->nullable()->unsigned();
            $table->foreign('class_id')->references('id')->on('aramisc_classes')->onDelete('cascade');


            $table->integer('section_id')->nullable()->unsigned();
            $table->foreign('section_id')->references('id')->on('aramisc_sections')->onDelete('cascade');
            
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('teacher_id')->nullable()->unsigned();
            $table->foreign('teacher_id')->references('id')->on('aramisc_staffs')->onDelete('cascade');

            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
            
            $table->integer('academic_id')->nullable()->default(1)->unsigned();
            $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');
        });

        // $sql ="INSERT INTO aramisc_exam_schedules 
        //     (id, class_id, section_id, exam_term_id, exam_id, exam_period_id, subject_id, date, room_id, active_status, created_by, updated_by, created_at, updated_at) 
        //     VALUES
        //     (1, 1, 1, 1, NULL, 8, 1, '2019-05-31', 3, 1, 1, 1, '2019-05-31 08:29:51', '2019-05-31 08:29:51'),
        //     (2, 1, 1, 1, NULL, 9, 2, '2019-05-25', 7, 1, 1, 1, '2019-05-31 08:30:02', '2019-05-31 08:30:02'),
        //     (3, 1, 1, 1, NULL, 8, 3, '2019-06-08', 1, 1, 1, 1, '2019-05-31 08:30:16', '2019-05-31 08:30:16'),
        //     (4, 1, 2, 1, NULL, 8, 1, '2019-05-26', 1, 1, 1, 1, '2019-05-31 08:30:50', '2019-05-31 08:30:50'),
        //     (5, 1, 2, 1, NULL, 10, 2, '2019-05-26', 1, 1, 1, 1, '2019-05-31 08:31:10', '2019-05-31 08:31:10'),
        //     (6, 1, 2, 1, NULL, 9, 3, '2019-06-01', 4, 1, 1, 1, '2019-05-31 08:31:25', '2019-05-31 08:31:25'),
        //     (7, 1, 3, 1, NULL, 8, 1, '2019-04-28', 3, 1, 1, 1, '2019-05-31 08:32:09', '2019-05-31 08:32:09'),
        //     (8, 1, 3, 1, NULL, 8, 2, '2019-05-18', 4, 1, 1, 1, '2019-05-31 08:32:22', '2019-05-31 08:32:22'),
        //     (9, 1, 3, 1, NULL, 8, 3, '2019-05-31', 3, 1, 1, 1, '2019-05-31 08:32:37', '2019-05-31 08:32:37')";
        // DB::insert($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_exam_schedules');
    }
}
