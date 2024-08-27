<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Model;
use App\Models\DirectFeesInstallmentAssign;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscBankPaymentSlip extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_bank_payment_slips';
    public function studentInfo(){
    	return $this->belongsTo('App\AramiscStudent', 'student_id', 'id');
    }
    public function feesType(){
    	return $this->belongsTo('App\AramiscFeesType', 'fees_type_id', 'id');
    }
    public function bank(){
    	return $this->belongsTo('App\AramiscBankAccount', 'bank_id', 'id');
    }
    public function feesInstallment(){
    	return $this->belongsTo('Modules\University\Entities\UnFeesInstallmentAssign', 'un_fees_installment_id', 'id');
    }

    public function installmentAssign(){
    	return $this->belongsTo(DirectFeesInstallmentAssign::class, 'installment_id', 'id');
    }
}
