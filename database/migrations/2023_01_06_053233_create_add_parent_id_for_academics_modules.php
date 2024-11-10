<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddParentIdForAcademicsModules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $name ="parent_id";

        if (!Schema::hasColumn('aramisc_sections', $name)) {
            Schema::table('aramisc_sections', function ($table) use ($name) {
                $table->integer($name)->nullable();
            });
        }
        if (!Schema::hasColumn('aramisc_classes', $name)) {
            Schema::table('aramisc_classes', function ($table) use ($name) {
                $table->integer($name)->nullable();
            });
        }
        if (!Schema::hasColumn('aramisc_class_sections', $name)) {
            Schema::table('aramisc_class_sections', function ($table) use ($name) {
                $table->integer($name)->nullable();
            });
        }

        if (!Schema::hasColumn('aramisc_subjects', $name)) {
            Schema::table('aramisc_subjects', function ($table) use ($name) {
                $table->integer($name)->nullable();
            });
        }

        if (!Schema::hasColumn('aramisc_assign_subjects', $name)) {
            Schema::table('aramisc_assign_subjects', function ($table) use ($name) {
                $table->integer($name)->nullable();
            });
        }
        if (!Schema::hasColumn('aramisc_teacher_upload_contents', $name)) {
            Schema::table('aramisc_teacher_upload_contents', function ($table) use ($name) {
                $table->integer($name)->nullable();
            });
        }

        if (!Schema::hasColumn('aramisc_exams', $name)) {
            Schema::table('aramisc_exams', function ($table) use ($name) {
                $table->integer($name)->nullable();
            });
        }

        
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('add_parent_id_for_academics_modules');
    }
}
