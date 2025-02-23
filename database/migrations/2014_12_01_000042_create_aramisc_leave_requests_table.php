<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAramiscLeaveRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_leave_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->date('apply_date')->nullable();
            $table->date('leave_from')->nullable();
            $table->date('leave_to')->nullable();
            $table->text('reason')->nullable();
            $table->text('note')->nullable();
            $table->string('file')->nullable();
            $table->string('approve_status')->nullable()->comment('P for Pending, A for Approve, R for reject');
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();


            $table->integer('leave_define_id')->nullable()->unsigned();
            $table->foreign('leave_define_id')->references('id')->on('aramisc_leave_defines')->onDelete('cascade');

            $table->integer('staff_id')->nullable()->unsigned();
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('role_id')->nullable()->unsigned();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            $table->integer('type_id')->nullable()->unsigned();
            $table->foreign('type_id')->references('id')->on('aramisc_leave_types')->onDelete('cascade');

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
        Schema::dropIfExists('aramisc_leave_requests');
    }
}
