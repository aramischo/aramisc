<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\AramiscItem;
class CreateAramiscItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sm_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('item_name',100)->nullable();
            $table->float('total_in_stock')->nullable();
            $table->string('description',500)->nullable();
            $table->timestamps();

            $table->integer('item_category_id')->nullable()->unsigned();
            $table->foreign('item_category_id')->references('id')->on('sm_item_categories')->onDelete('cascade');   

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');   
            
            $table->integer('academic_id')->nullable()->default(1)->unsigned();
            $table->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sm_items');
    }
}
