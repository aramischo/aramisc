<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscBookIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_book_issues', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('quantity')->nullable();
            $table->date('given_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('issue_status')->nullable();
            $table->string('note', 500)->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

            $table->integer('book_id')->nullable()->unsigned();
            $table->foreign('book_id')->references('id')->on('aramisc_books')->onDelete('cascade');

            $table->integer('member_id')->nullable()->unsigned();
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
            
            $table->integer('academic_id')->nullable()->default(1)->unsigned();
            $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');
        });

        //  Schema::table('aramisc_book_issues', function($table) {
        //     $table->foreign('member_id')->references('id')->on('aramisc_library_members');
        //     $table->foreign('book_id')->references('id')->on('aramisc_books');

        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_book_issues');
    }
}
