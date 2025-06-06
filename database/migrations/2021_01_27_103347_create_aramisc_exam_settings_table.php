<?php

use App\AramiscExamSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAramiscExamSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_exam_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('exam_type')->nullable();
            $table->string('title')->nullable();
            $table->date('publish_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('file', 200)->nullable();
            $table->tinyInteger('active_status')->nullable()->default(1);
            $table->timestamps();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');   
            
            $table->integer('academic_id')->nullable()->default(1)->unsigned();
            $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');
        });

        $store = new AramiscExamSetting();
        $store->exam_type = 1;
        $store->title = 'Exam Controller';
        $store->publish_date = date('Y-m-d h:i:s');
        $store->start_date = date('Y-m-d h:i:s');
        $store->end_date = date('Y-m-d h:i:s');
        $store->file = "public/uploads/exam/signature.png";
        $store->save();
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_exam_settings');
    }
}
