<?php

namespace App;

use App\AramiscPaymentGatewaySetting;
use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscPaymentMethhod extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_payment_methhods';
    protected $casts = [
        'id' => 'integer',
        'method' => 'string',
    ];

    
    protected Static function boot(){
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
    
    public function incomeAmounts()
    {
        return $this->hasMany('App\AramiscAddIncome', 'payment_method_id');
    }

    public function getIncomeAmountAttribute()
    {
        return $this->incomeAmounts->sum('amount');
    }

    public function expenseAmounts()
    {
        return $this->hasMany('App\AramiscAddExpense', 'payment_method_id');
    }

    public function getExpenseAmountAttribute()
    {
        return $this->expenseAmounts->sum('amount');
    }

    public function gatewayDetail()
    {
        return $this->hasOne(AramiscPaymentGatewaySetting::class,'gateway_name','method')->where('school_id',auth()->user()->school_id);
    }

}
