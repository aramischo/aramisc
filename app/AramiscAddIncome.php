<?php

namespace App;

use App\AramiscAddExpense;
use Illuminate\Support\Facades\Auth;
use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscAddIncome extends Model
{
    use HasFactory;
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);

    }  
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_add_incomes';  
    protected $guerded = ['id']; 
    protected $casts = [ 'date' => 'date' ]; 
    
    public function incomeHeads()
    {
        return $this->belongsTo('App\AramiscIncomeHead', 'income_head_id', 'id');
    }

    public function ACHead()
    {
        return $this->belongsTo('App\AramiscChartOfAccount', 'income_head_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo('App\AramiscBankAccount', 'account_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('App\AramiscPaymentMethhod', 'payment_method_id', 'id');
    }

    public function scopeAddIncome($query, $date_from, $date_to, $payment_method)
    {
        return $query->where('date', '>=', $date_from)
            ->where('date', '<=', $date_to)
            ->where('active_status', 1)
            ->where('school_id', Auth::user()->school_id)
            ->where('payment_method_id', $payment_method);
    }

    public static function monthlyIncome($i)
    {
        try {
            if(moduleStatusCheck('University')){
                $m_total_income = AramiscAddIncome::where('un_academic_id', getAcademicId())
                                ->where('name', '!=', 'Fund Transfer')
                                ->where('school_id', Auth::user()->school_id)
                                ->where('active_status', 1)
                                ->where('date', 'like', date('Y-m-') . $i)
                                ->sum('amount');
            }else{
                $m_total_income = AramiscAddIncome::where('academic_id', getAcademicId())
                                ->where('name', '!=', 'Fund Transfer')
                                ->where('school_id', Auth::user()->school_id)
                                ->where('active_status', 1)
                                ->where('date', 'like', date('Y-m-') . $i)
                                ->sum('amount');
            }
            return $m_total_income;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function monthlyExpense($i)
    {
        try {
            if(moduleStatusCheck('University')){
                $m_total_expense = AramiscAddExpense::where('un_academic_id', getAcademicId())
                                ->where('name', '!=', 'Fund Transfer')
                                ->where('school_id', Auth::user()->school_id)
                                ->where('active_status', 1)
                                ->where('date', 'like', date('Y-m-') . $i)
                                ->sum('amount');
            }else{
                $m_total_expense = AramiscAddExpense::where('academic_id', getAcademicId())
                                ->where('name', '!=', 'Fund Transfer')
                                ->where('school_id', Auth::user()->school_id)
                                ->where('active_status', 1)
                                ->where('date', 'like', date('Y-m-') . $i)
                                ->sum('amount');
            }
            return $m_total_expense;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function yearlyIncome($i)
    {
        try {
            if(moduleStatusCheck('University')){
                $y_total_income = AramiscAddIncome::where('un_academic_id', getAcademicId())
                                ->where('name', '!=', 'Fund Transfer')
                                ->where('school_id', Auth::user()->school_id)
                                ->where('active_status', 1)
                                ->where('date', 'like', date('Y-' . $i) . '%')
                                ->sum('amount');
            }else{
                $y_total_income = AramiscAddIncome::where('academic_id', getAcademicId())
                                ->where('name', '!=', 'Fund Transfer')
                                ->where('school_id', Auth::user()->school_id)
                                ->where('active_status', 1)
                                ->where('date', 'like', date('Y-' . $i) . '%')
                                ->sum('amount');
            }
            return $y_total_income;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function yearlyExpense($i)
    {
        try {
            $m_add_expenses = AramiscAddExpense::where('academic_id', getAcademicId())
                ->where('name', '!=', 'Fund Transfer')
                ->where('school_id', Auth::user()->school_id)
                ->where('active_status', 1)
                ->where('date', 'like', date('Y-' . $i) . '%')
                ->sum('amount');
            $m_total_expense = $m_add_expenses;
            return $m_total_expense;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }
}
