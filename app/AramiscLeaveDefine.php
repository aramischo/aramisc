<?php

namespace App;

use Carbon\Carbon;
use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscLeaveDefine extends Model
{
    use HasFactory;
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
     // Spécifiez le nom de la table explicitement
     protected $table = 'sm_leave_defines';
    public function role(){
    	return $this->belongsTo('Modules\RolePermission\Entities\InfixRole', 'role_id', 'id');
    }

    public function leaveType(){
    	return $this->belongsTo('App\AramiscLeaveType', 'type_id', 'id');
    }

    public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function leaveRequests(){
    	return $this->hasMany(AramiscLeaveRequest::class, 'leave_define_id')->where('approve_status','=','A');
    }

    public function getremainingDaysAttribute()
	{
        $diff_in_days = 0;
        foreach($this->leaveRequests as $leave){
            $to = Carbon::parse( $leave->leave_from);
		    $from = Carbon::parse( $leave->leave_to);
            $diff_in_days = $to->diffInDays($from)+1;
        }
        return $diff_in_days;
	}
}