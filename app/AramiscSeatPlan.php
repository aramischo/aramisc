<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscSeatPlan extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_seat_plans';
    public function seatPlanChild(){
    	return $this->hasMany('App\AramiscSeatPlanChild', 'seat_plan_id', 'id');
    }
    public function exam(){
    	return $this->belongsTo('App\AramiscExam', 'exam_id', 'id');
    }
    public function subject(){
    	return $this->belongsTo('App\AramiscSubject', 'subject_id', 'id');
    }
    public function section(){
    	return $this->belongsTo('App\AramiscSection', 'section_id', 'id');
    }
    public function class(){
    	return $this->belongsTo('App\AramiscClass', 'class_id', 'id');
    }

    public static function total_student($class, $section){
        try {
            return AramiscStudent::where('class_id', $class)->where('section_id', $section)->count();
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }
}
