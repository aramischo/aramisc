<?php

use App\AramiscHeaderMenuManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscHeaderMenuManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_header_menu_managers', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('element_id')->nullable();
            $table->string('title')->nullable();
            $table->string('link')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('show')->default(0);
            $table->boolean('is_newtab')->default(0);
            $table->string('theme')->default('default');
            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');

            $table->timestamps();
        });

        // $store = new AramiscHeaderMenuManager();
        // $store->id = 1;
        // $store->type = 'sPages';
        // $store->element_id = 1;
        // $store->title = 'Home';
        // $store->link = '/';
        // $store->save();

        // $store = new AramiscHeaderMenuManager();
        // $store->id = 2;
        // $store->type = 'sPages';
        // $store->element_id = 2;
        // $store->title = 'About';
        // $store->link = '/about';
        // $store->save();

        // $store = new AramiscHeaderMenuManager();
        // $store->id = 3;
        // $store->type = 'sPages';
        // $store->element_id = 3;
        // $store->title = 'Course';
        // $store->link = '/course';
        // $store->save();

        // $store = new AramiscHeaderMenuManager();
        // $store->id = 4;
        // $store->type = 'sPages';
        // $store->element_id = 4;
        // $store->title = 'News';
        // $store->link = '/news-page';
        // $store->save();

        // $store = new AramiscHeaderMenuManager();
        // $store->id = 5;
        // $store->type = 'sPages';
        // $store->element_id = 5;
        // $store->title = 'Contact';
        // $store->link = '/contact';
        // $store->save();

        // $store = new AramiscHeaderMenuManager();
        // $store->id = 6;
        // $store->type = 'sPages';
        // $store->element_id = 6;
        // $store->title = 'Login';
        // $store->link = '/login';
        // $store->save();

        // $store = new AramiscHeaderMenuManager();
        // $store->id = 7;
        // $store->type = 'sPages';
        // $store->element_id = 7;
        // $store->title = 'Result';
        // $store->link = '/exam-result';
        // $store->save();

        // $store = new AramiscHeaderMenuManager();
        // $store->id = 8;
        // $store->type = 'sPages';
        // $store->element_id = 8;
        // $store->title = 'Routine';
        // $store->link = '/class-exam-routine';
        // $store->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_header_menu_managers');
    }
}
