<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscExamTypesTable extends Migration
{
    public function up()
    {
        Schema::create('aramisc_exam_types', function (Blueprint $table) {
            $table->increments('id');
            $table->Integer('active_status')->default(1);
            $table->string('title', 255);
            $table->tinyInteger('is_average')->default(0);
            $table->float('percentage')->nullable();
            $table->float('average_mark')->default(0);
            $table->timestamps();
            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
            
            $table->integer('academic_id')->nullable()->default(1)->unsigned();
            $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');

            $table->integer('parent_id')->nullable()->default(0)->unsigned();
        });

        // DB::table('aramisc_exam_types')->insert([

        //     [
        //         'school_id'=> 1,
        //         'active_status'=> 1,
        //         'title' => 'First Term'
        //     ],
        //     [
        //         'school_id'=> 1,
        //         'active_status'=> 1,
        //         'title' => 'Second Term'
        //     ],
        //     [
        //         'school_id'=> 1,
        //         'active_status'=> 1,
        //         'title' => 'Third Term'
        //     ],

        //    ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_exam_types');
    }
}
