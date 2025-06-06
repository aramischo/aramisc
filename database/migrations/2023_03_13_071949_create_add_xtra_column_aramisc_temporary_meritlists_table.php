<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddXtraColumnAramiscTemporaryMeritlistsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('aramisc_temporary_meritlists')) {
            Schema::table('aramisc_temporary_meritlists', function (Blueprint $table) {
                if (!Schema::hasColumn('aramisc_temporary_meritlists', 'roll_no')) {
                    $table->integer('roll_no')->nullable();
                }
            });
        }

            Schema::table('custom_result_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('custom_result_settings', 'vertical_boarder')) {
                    $table->string('vertical_boarder')->nullable();
                }
            });

    }

    public function down()
    {

            Schema::table('aramisc_temporary_meritlists', function (Blueprint $table) {
                if (Schema::hasColumn('aramisc_temporary_meritlists', 'roll_no')) {
                    $table->dropColumn('roll_no');
                }
            });
        Schema::table('custom_result_settings', function (Blueprint $table) {
            if (Schema::hasColumn('custom_result_settings', 'vertical_boarder')) {
                $table->dropColumn('vertical_boarder');
            }
        });
    }
}
