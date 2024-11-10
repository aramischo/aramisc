<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;

class AramiscFeesAssign extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_fees_assigns';
    public function feesGroupMaster(){
        return $this->belongsTo('App\AramiscFeesMaster', 'fees_master_id', 'id');
    }


    public function feesGroupMasterApi(){
        return $this->belongsTo('App\AramiscFeesMaster', 'fees_master_id', 'id')->withoutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function feesPayments(){
        return $this->hasMany('App\AramiscFeesPayment', 'assign_id');
    }


    public function getApplyDiscountSumAttribute()
    {
        return $this->feesPayments()->where('student_id', $this->student_id)->where('record_id', $this->record_id)->where('fees_type_id', $this->feesGroupMaster->feesTypes->id)->sum('applied_discount');
    }
    public function getDiscountSumAttribute()
    {
        return $this->feesPayments()->where('student_id', $this->student_id)->where('fees_type_id', $this->feesGroupMaster->feesTypes->id)->sum('discount_amount');
    }
    public function getTotalPaidAttribute()
    {
        return $this->feesPayments()->where('student_id', $this->student_id)->where('fees_type_id', $this->feesGroupMaster->feesTypes->id)->sum('amount');
    }
    public function getTotalFineAttribute()
    {
        return $this->feesPayments()->where('student_id', $this->student_id)->where('fees_type_id', $this->feesGroupMaster->feesTypes->id)->sum('fine');
    }

    public static function discountSum($student_id, $type_id, $perpose, $record_id)
    {
        try {
            $sum = AramiscFeesPayment::where('active_status',1)
                ->where('student_id', $student_id)
                ->where('record_id', $record_id)
                ->where('fees_type_id', $type_id)
                ->sum($perpose);

            return $sum;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    
    public static function groups($student_id){
      
        $fees_assigneds=AramiscFeesAssign::where('student_id',$student_id)
        ->where('academic_id', getAcademicId())->where('school_id',auth()->user()->school_id)                             
        ->get();
        return $fees_assigneds;
    }

    public static function createdBy($student_id, $discount_id, $record_id){

        try {
            $created_by = AramiscFeesPayment::where('active_status',1)
                        ->where('student_id', $student_id)
                        ->where('record_id', $record_id)
                        ->where('fees_discount_id', $discount_id)
                        ->first();
            return $created_by;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }

    public static function feesPayment($type_id, $student_id, $record_id){
        try {
            $payments = AramiscFeesPayment::where('active_status',1)
                        ->where('fees_type_id', $type_id)
                        ->where('student_id', $student_id)
                        ->where('record_id', $record_id)
                        ->get();
            return $payments;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }
    public static function studentFeesTypeDiscount($group_id, $student_id,$discount_amount,$record_id){
        try {
            $assigned_fees_type=AramiscFeesAssign::where('student_id',$student_id)
                ->where('record_id',$record_id)
                ->join('aramisc_fees_masters','aramisc_fees_masters.id','=','aramisc_fees_assigns.fees_master_id')
                ->join('aramisc_fees_types','aramisc_fees_types.id','=','aramisc_fees_masters.fees_type_id')
                ->where('aramisc_fees_masters.fees_group_id','=',$group_id)
                ->where('aramisc_fees_assigns.applied_discount','=',null)
                ->where('aramisc_fees_assigns.fees_amount','>',0)
                ->select('aramisc_fees_masters.id','aramisc_fees_types.id as fees_type_id','name','amount','aramisc_fees_assigns.student_id','applied_discount','aramisc_fees_masters.fees_group_id')
                ->get();
            return $assigned_fees_type;
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }
    public function studentInfo(){
        return $this->belongsTo('App\AramiscStudent', 'student_id', 'id');
    }

    public function recordDetail(){
        return $this->belongsTo('App\Models\StudentRecord', 'record_id', 'id');
    }

}