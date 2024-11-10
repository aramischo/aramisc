<?php

use App\AramiscSchool;
use App\Models\AramiscCalendarSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aramisc_calendar_settings', function (Blueprint $table) {
            $table->id();
            $table->string('menu_name');
            $table->tinyInteger('status')->default(0);
            $table->string('font_color');
            $table->string('bg_color');

            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->foreign('school_id')->references('id')->on('aramisc_schools')->onDelete('cascade');
            $table->timestamps();
        });

        $menuNames = [
            '#008000' => 'admission_query', 
            '#000000' => 'lesson_plan', 
            '#FF0000' => 'homework', 
            '#800080' => 'study_material', 
            '#000080' => 'exam', 
            '#808000' => 'online_exam', 
            '#008080' => 'leave', 
            '#00FFFF' => 'notice_board',
            '#808080' => 'holiday', 
            '#800000' => 'event',
            '#800009' => 'library',
        ];

        $schools = AramiscSchool::get();
        foreach($schools as $school){
            foreach($menuNames as $key => $menuName){
                $storeData = new AramiscCalendarSetting();
                $storeData->menu_name = $menuName;
                $storeData->status = 1;
                $storeData->font_color = '#FFFFFF';
                $storeData->bg_color = $key;
                $storeData->school_id = $school->id;
                $storeData->save();
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('aramisc_calendar_settings');
    }
};
