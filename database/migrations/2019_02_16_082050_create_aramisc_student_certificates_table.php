<?php

use App\AramiscStudentCertificate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscStudentCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('aramisc_student_certificates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('header_left_text')->nullable();
            $table->date('date')->nullable();
            $table->text('body')->nullable();
            $table->text('body_two')->nullable();
            $table->text('certificate_no')->nullable();
            $table->string('type')->nullable()->default('school');
            $table->string('footer_left_text')->nullable();
            $table->string('footer_center_text')->nullable();
            $table->string('footer_right_text')->nullable();
            $table->tinyInteger('student_photo')->default(1)->comment('1 = yes 0 no');
            $table->string('file')->nullable();
            $table->integer('layout')->nullable()->comment('1 = Portrait, 2 =  Landscape');
            $table->string('body_font_family')->nullable()->default('Arial')->comment('body_font_family');
            $table->string('body_font_size')->nullable()->default('2em')->comment('');
            $table->string('height', 50)->nullable()->comment('Height in mm');
            $table->string('width', 50)->nullable()->comment('width in mm');
            $table->string('default_for', 50)->nullable()->comment('default_for course');
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();


            $table->integer('created_by')->nullable()->default(1)->unsigned();
            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
            
            $table->integer('academic_id')->nullable()->default(1)->unsigned();
            $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');
        });


        $s                      = new AramiscStudentCertificate();
        $s->name                = 'Certificate in Technical Communication (PCTC)';
        $s->header_left_text    = 'Since 2020';
        $s->date                = '2020-05-17';
        $s->body                = 'Earning my UCR Extension professional certificate is one of the most beneficial things I\'ve done for my career. Before even completing the program, I was contacted twice by companies who were interested in hiring me as a technical writer. This program helped me reach my career goals in a very short time';
        $s->footer_left_text    = 'Advisor Signature';
        $s->footer_center_text  = 'Instructor Signature';
        $s->footer_right_text = 'Principale Signature';
        $s->student_photo       = 0;
        $s->body_font_family       = 'Arial';
        $s->body_font_size       = '2em';
        $s->file                = 'public/uploads/certificate/c.jpg';
        $s->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_student_certificates');

    }
}
