<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscHrPayrollEarnDeduc extends Model
{
	use HasFactory;
	// Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_hr_payroll_earn_deducs';
	public static function boot()
    {
        parent::boot();
		static::addGlobalScope(new AcademicSchoolScope);
    }

    public static function getTotalEarnings($payroll_generate_id){
    	 
		try {
			$totalEarnings = AramiscHrPayrollEarnDeduc::where('payroll_generate_id', $payroll_generate_id)
								->where('earn_dedc_type', 'E')
								->sum('amount');

				if(isset($totalEarnings)){
					return $totalEarnings;
				}
				else{
					return false;
				}
		} catch (\Exception $e) {
			return false;
		}
    }

    public static function getTotalDeductions($payroll_generate_id){
    	 
		try {
			$totalDeductions = AramiscHrPayrollEarnDeduc::where('payroll_generate_id', $payroll_generate_id)
								->where('earn_dedc_type', 'D')
								->sum('amount');

				if(isset($totalDeductions)){
					return $totalDeductions;
				}
				else{
					return false;
				}
		} catch (\Exception $e) {
			return false;
		}
    }
    
}
