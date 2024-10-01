<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Models\DirectFeesInstallmentAssign;
use App\Models\DireFeesInstallmentChildPayment;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscFeesPayment extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_fees_payments';
    protected $guarded = ['id'];
    
    public function studentInfo()
    {
        return $this->belongsTo('App\AramiscStudent', 'student_id', 'id');
    }

    public function feesType()
    {
        return $this->belongsTo('App\AramiscFeesType', 'fees_type_id', 'id');
    }

    public function feesMaster()
    {
        return $this->belongsTo('App\AramiscFeesMaster', 'fees_type_id', 'fees_type_id');
    }

    public static function discountMonth($discount, $month)
    {
        try {
            return AramiscFeesPayment::where('active_status', 1)->where('fees_discount_id', $discount)->where('discount_month', $month)->first();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public function recordDetail()
    {
        return $this->belongsTo('App\Models\StudentRecord', 'record_id', 'id');
    }

    public function feesInstallment()
    {
            if(moduleStatusCheck('University')){
                return $this->belongsTo('Modules\University\Entities\UnFeesInstallmentAssign', 'un_fees_installment_id', 'id');
            }
            else{
                return $this->belongsTo(DirectFeesInstallmentAssign::class, 'direct_fees_installment_assign_id', 'id');
            }
    }


    
    public function installmentPayment()
    {
            if(moduleStatusCheck('University')){
                return $this->belongsTo('Modules\University\Entities\UnFeesInstallAssignChildPayment', 'installment_payment_id', 'id');
            }
            else{
                return $this->belongsTo(DireFeesInstallmentChildPayment::class, 'installment_payment_id', 'id');
            }
    }
        
}
