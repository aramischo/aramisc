<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscComplaintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_complaints', function (Blueprint $table) {
            $table->increments('id');
            $table->string('complaint_by')->nullable();
            $table->tinyInteger('complaint_type')->nullable();
            $table->tinyInteger('complaint_source')->nullable();
            $table->string('phone')->nullable();
            $table->date('date')->nullable();
            $table->text('description')->nullable();
            $table->string('action_taken')->nullable();
            $table->string('assigned')->nullable();
            $table->string('file')->nullable();
            $table->tinyInteger('active_status')->default(1);
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
        Schema::dropIfExists('aramisc_complaints');
    }
}
