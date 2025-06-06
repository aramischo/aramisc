<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscFeesMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_fees_masters', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date')->nullable();
           
            $table->float('amount', 10, 2)->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

            $table->integer('fees_group_id')->nullable()->unsigned();
            $table->foreign('fees_group_id')->references('id')->on('aramisc_fees_groups')->onDelete('cascade');

            $table->integer('fees_type_id')->nullable()->unsigned();
            $table->foreign('fees_type_id')->references('id')->on('aramisc_fees_types')->onDelete('cascade');

            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
            
            $table->integer('academic_id')->nullable()->default(1)->unsigned();
            $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');
            $table->integer('class_id')->nullable()->unsigned();
            $table->integer('section_id')->nullable()->unsigned();
            $table->integer('un_semester_label_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_fees_masters');
    }
}
