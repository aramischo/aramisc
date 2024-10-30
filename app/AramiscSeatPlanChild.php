<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscSeatPlanChild extends Model
{
    use HasFactory;
    public function class_room(){
    	return $this->belongsTo('App\AramiscClassRoom', 'room_id', 'id');
    }
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_seat_plan_children';
    public static function usedRoomCapacity($room_id){
    	return AramiscSeatPlanChild::where('room_id', $room_id)->sum('assign_students');
    }
}
