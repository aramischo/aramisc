<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\AramiscHomePageSetting;
class CreateAramiscHomePageSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_home_page_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title',255)->nullable();
            $table->string('long_title',255)->nullable();
            $table->text('short_description')->nullable();
            $table->string('link_label',255)->nullable();
            $table->string('link_url',255)->nullable();
            $table->string('image',255)->nullable();
            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
            $table->timestamps();
        });

        $s = new AramiscHomePageSetting();
        $s->title = 'THE ULTIMATE EDUCATION ERP';
        $s->long_title = 'Aramisc';
        $s->short_description = 'Managing various administrative tasks in one place is now quite easy and time savior with this Aramisc and Give your valued time to your institute that will increase next generation productivity for our society.';
        $s->link_label = 'Learn More About Us';
        $s->link_url = 'http://aramiscdu.com/about';
        $s->image = 'public/backEnd/img/client/home-banner1.jpg';
        $s->save();
    } 
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_home_page_settings');
    }
}
