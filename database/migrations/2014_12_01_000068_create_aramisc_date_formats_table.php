<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\AramiscDateFormat;

class CreateAramiscDateFormatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_date_formats', function (Blueprint $table) {
            $table->increments('id');
            $table->string('format')->nullable();
            $table->string('normal_view')->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
        });





        $data = [

            ['jS M, Y', '17th May, 2019'],

            ['Y-m-d', '2019-05-17'],
            ['Y-d-m', '2019-17-05'],
            ['d-m-Y', '17-05-2019'],
            ['m-d-Y', '05-17-2019'],

            ['Y/m/d', '2019/05/17'],
            ['Y/d/m', '2019/17/05'],
            ['d/m/Y', '17/05/2019'],
            ['Y-m-d', '05/17/2019'],

            ['l jS \of F Y', 'Monday 17th of May 2019'],
            ['jS \of F Y', '17th of May 2019'],
            ['g:ia \o\n l jS F Y', '12:00am on Monday 17th May 2019'],
            ['F j, Y, g:i a', 'May 7, 2019, 6:20 pm'],
            ['F j, Y', 'May 17, 2019'],
            ['\i\t \i\s \t\h\e jS \d\a\y', 'it is the 17th day']
        ];

        foreach ($data as $dateFormate) {
            $store = new AramiscDateFormat();
            $store->format = $dateFormate[0];
            $store->normal_view = $dateFormate[1];
            $store->created_at = date('Y-m-d h:i:s');
            $store->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_date_formats');
    }
}
