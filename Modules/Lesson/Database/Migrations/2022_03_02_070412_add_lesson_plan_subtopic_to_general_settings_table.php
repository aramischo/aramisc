<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\RolePermission\Entities\AramiscModuleInfo;

class AddLessonPlanSubtopicToGeneralSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('aramisc_general_settings', function (Blueprint $table) {
            if(!Schema::hasColumn($table->getTable(), 'sub_topic_enable')){
                $table->boolean('sub_topic_enable')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('aramisc_general_settings', function (Blueprint $table) {
            if(Schema::hasColumn($table->getTable(), 'sub_topic_enable')){
                $table->dropColumn('sub_topic_enable');
            }
        });

        \Modules\RolePermission\Entities\AramiscModuleInfo::where('id', 835)->delete();
    }
}
