<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\AramiscRoomList;
class CreateAramiscRoomListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_room_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->integer('number_of_bed');
            $table->double('cost_per_bed',16,2)->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

            $table->integer('dormitory_id')->nullable()->default(1)->unsigned();
            $table->foreign('dormitory_id')->references('id')->on('aramisc_dormitory_lists')->onDelete('cascade');

            $table->integer('room_type_id')->nullable()->default(1)->unsigned();
            $table->foreign('room_type_id')->references('id')->on('aramisc_room_types')->onDelete('cascade');

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
        Schema::dropIfExists('aramisc_room_lists');
    }
}
