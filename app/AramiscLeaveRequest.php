<?php

namespace App;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscLeaveRequest extends Model
{
	use HasFactory;
    // Spécifiez le nom de la table explicitement
	protected $table = 'aramisc_leave_requests';
	protected $casts = [
		'id' => 'integer',
		'apply_date' => 'string',
		'leave_from' => 'string',
		'leave_to' => 'string',
		'reason' => 'string',
		'file' => 'string',
		'leave_define_id' => 'integer',
	];	
    public function leaveType()
	{
	  return $this->belongsTo('App\AramiscLeaveType', 'type_id');
	}

	public function leaveDefine()
	{
	  return $this->belongsTo('App\AramiscLeaveDefine', 'leave_define_id', 'id');
	}

	public function staffs()
	{
	  return $this->belongsTo('App\AramiscStaff', 'staff_id', 'user_id');
	}
	public function student()
	{
	  return $this->belongsTo('App\AramiscStudent', 'staff_id', 'user_id');
	}

	public function user()
	{
	  return $this->belongsTo('App\Models\User', 'staff_id', 'id');
	}

	public function getRemainingDaysAttribute()
	{
		$to = Carbon::parse( $this->leave_from);
		$from = Carbon::parse( $this->leave_to);
		$diff_in_days = $to->diffInDays($from);		
		return $diff_in_days;
	}

	public static function approvedLeave($type_id){
		
		try {
			$user = Auth::user();
				$leaves = AramiscLeaveRequest::where('role_id', $user->role_id)->where('staff_id', $user->id)
				->where('leave_define_id', $type_id)->where('approve_status', "A")->get();
				
				$approved_days = 0;
				foreach($leaves as $leave){
					$start = strtotime($leave->leave_from);
					$end = strtotime($leave->leave_to);
					$days_between = ceil(abs($end - $start) / 86400);
					$days = $days_between + 1;
					$approved_days += $days;
				}
				return $approved_days;
		} catch (\Exception $e) {
			$data=[];
			return $data;
		}
	}

	public static function approvedLeaveModal($type_id, $role_id, $staff_id){
		
		try {
			$leaves = AramiscLeaveRequest::where('role_id', $role_id)->where('staff_id', $staff_id)->where('leave_define_id', $type_id)->where('approve_status', "A")->get();
				$approved_days = 0;
				foreach($leaves as $leave){
					$start = strtotime($leave->leave_from);
					$end = strtotime($leave->leave_to);
					$days_between = ceil(abs($end - $start) / 86400);
					$days = $days_between + 1;
					$approved_days += $days;
				}
				return $approved_days;
		} catch (\Exception $e) {
			$data=[];
			return $data;
		}
	}
}