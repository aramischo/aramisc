<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscBackgroundSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_background_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title',255)->nullable();
            $table->string('type',255)->nullable();
            $table->string('image',255)->nullable();
            $table->string('color',255)->nullable();
            $table->integer('is_default')->default(0);
            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
            $table->timestamps();
        });


        DB::table('aramisc_background_settings')->insert([
            [
                'id'            => 1,
                'title'         => 'Dashboard Background',
                'type'          => 'image',
                'image'         => 'public/backEnd/img/body-bg.jpg',
                'color'         => '',
                'is_default'    => 1,

            ],

            [
                'id'            => 2,
                'title'         => 'Login Background',
                'type'          => 'image',
                'image'         => 'public/backEnd/img/login-bg.jpg',
                'color'         => '',
                'is_default'    => 0,

            ],

        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_background_settings');
    }
}
