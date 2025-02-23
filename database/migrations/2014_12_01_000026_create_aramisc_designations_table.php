<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateAramiscDesignationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_designations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255)->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');

            // $table->integer('academic_id')->nullable()->default(1)->unsigned();
            // $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');
            
            $table->integer('is_saas')->nullable()->default(0)->unsigned();
        });


        DB::table('aramisc_designations')->insert([
            [
                'title' => 'Principal',
                'created_at' => date('Y-m-d h:i:s')
            ]
        ]);


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_designations');
    }
}
