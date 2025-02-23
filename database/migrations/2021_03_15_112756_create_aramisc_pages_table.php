<?php

use App\AramiscPage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAramiscPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_pages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->string('sub_title')->unique()->nullable();
            $table->string('slug')->nullable();
            $table->text('header_image')->nullable();
            $table->longText('details')->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->tinyInteger('is_dynamic')->default(1);

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');

            $table->timestamps();
        });

        $store = new AramiscPage();
        $store->id = 1;
        $store->title = 'Home';
        $store->slug = '/';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();

        $store = new AramiscPage();
        $store->id = 2;
        $store->title = 'About';
        $store->slug = '/about';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();

        $store = new AramiscPage();
        $store->id = 3;
        $store->title = 'Course';
        $store->slug = '/course';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();

        $store = new AramiscPage();
        $store->id = 4;
        $store->title = 'News';
        $store->slug = '/news-page';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();

        $store = new AramiscPage();
        $store->id = 5;
        $store->title = 'Contact';
        $store->slug = '/contact';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();

        $store = new AramiscPage();
        $store->id = 6;
        $store->title = 'Login';
        $store->slug = '/login';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();

        $store = new AramiscPage();
        $store->id = 7;
        $store->title = 'Result';
        $store->slug = '/exam-result';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();

        $store = new AramiscPage();
        $store->id = 8;
        $store->title = 'Routine';
        $store->slug = '/class-exam-routine';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_pages');
    }
}
