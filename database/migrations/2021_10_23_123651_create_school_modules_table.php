<?php

use App\Models\SchoolModule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_modules', function (Blueprint $table) {
            $table->id();
            $table->longText('modules')->nullable();
            $table->longText('menus')->nullable();
            $table->string('module_name')->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->integer('updated_by')->nullable();
            $table->integer('school_id')->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
            $table->timestamps();
        });

        if(moduleStatusCheck('Lead')){
            $schools = \App\AramiscSchool::all();
            foreach($schools as $school){
                $exists = SchoolModule::where('school_id', $school->id)->first();
                if (!$exists){
                    $settings = new SchoolModule;
                    $settings->module_name ='lead';
                    $settings->school_id = $school->id;
                    $settings->active_status = $school->id == 1 ? 1 : 0;
                    $settings->save();
                }
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('school_modules');
    }
}
