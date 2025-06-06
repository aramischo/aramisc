<?php

namespace App;

use App\Models\PayrollPayment;
use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscHrPayrollGenerate extends Model
{
	use HasFactory;
	public static function boot()
    {
        parent::boot();
		static::addGlobalScope(new AcademicSchoolScope);
    }
	// Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_hr_payroll_generates';
    public function staffs(){
		return $this->belongsTo('App\AramiscStaff', 'staff_id', 'id');
	}

	// public static function getPayrollDetails($staff_id, $payroll_month, $payroll_year){
	// 	try {
	// 		$getPayrollDetails = AramiscHrPayrollGenerate::select('id','payroll_status')
	// 							->where('staff_id', $staff_id)
	// 							->where('payroll_month', $payroll_month)
	// 							->where('payroll_year', $payroll_year)
	// 							->first();

	// 		if(isset($getPayrollDetails)){
	// 			return $getPayrollDetails;
	// 		}
	// 		else{
	// 			return false;
	// 		}
	// 	} catch (\Exception $e) {
	// 		return false;
	// 	}
    // }

	public function staffDetails(){
		return $this->belongsTo('App\AramiscStaff', 'staff_id', 'id');
	}

	public static function getPaymentMode($id){
		
		try {
			$getPayrollDetails = AramiscPaymentMethhod::select('method')
									->where('id', $id)
									->first();
				if(isset($getPayrollDetails)){
					return $getPayrollDetails->method;
				}
				else{
					return false;
				}
		} catch (\Exception $e) {
			return false;
		}
	}

	public function paymentMethods(){
		return $this->belongsTo('App\AramiscPaymentMethhod', 'payment_mode', 'id')->withDefault();
	}
	public function payrollPayments()
	{
		return $this->hasMany(PayrollPayment::class, 'aramisc_hr_payroll_generate_id', 'id');
	}
}
