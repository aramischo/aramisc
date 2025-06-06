<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscHolidaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_holidays', function (Blueprint $table) {
            $table->increments('id');
            $table->string('holiday_title', 200)->nullable();
            $table->string('details', 500)->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->string('upload_image_file', 200)->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();


            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
            
            $table->integer('academic_id')->nullable()->default(1)->unsigned();
            $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');
        });

        // DB::table('aramisc_holidays')->insert([
        //     [
        //         'holiday_title'=>'Summer Vacation',
        //         'from_date'=>'2019-05-02',
        //         'to_date'=>'2019-05-08',
        //     ],
        //     [
        //         'holiday_title'=>'Public Holiday',
        //         'from_date'=>'2019-05-010',
        //         'to_date'=>'2019-05-11',
        //     ],
        // ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_holidays');
    }
}
