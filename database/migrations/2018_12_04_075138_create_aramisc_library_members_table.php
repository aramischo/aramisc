<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\AramiscLibraryMember;

class CreateAramiscLibraryMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aramisc_library_members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('member_ud_id')->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

            $table->integer('member_type')->nullable()->unsigned();
            $table->foreign('member_type')->references('id')->on('roles')->onDelete('cascade');

            $table->integer('student_staff_id')->nullable()->unsigned();
            $table->foreign('student_staff_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
            
            $table->integer('academic_id')->nullable()->default(1)->unsigned();
            $table->foreign('academic_id')->references('id')->on('aramisc_academic_years')->onDelete('cascade');
        });


        // $member_ud_id =['1001','2001','3001','5001'];
        // $member_type =['2','2','4','8'];
        // $student_staff_id =['2','14','6','4'];

        //  for($i=0; $i<4; $i++){
        //     $store = new AramiscLibraryMember();
        //     $store->member_ud_id = $member_ud_id[$i];
        //     $store->member_type =$member_type[$i];
        //     $store->student_staff_id =$student_staff_id[$i];
        //     $store->save();
        // }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aramisc_library_members');
    }
}
