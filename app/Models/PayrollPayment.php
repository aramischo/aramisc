<?php

namespace App\Models;

use App\AramiscAddExpense;
use App\AramiscBankStatement;
use App\AramiscPaymentMethhod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollPayment extends Model
{
    use HasFactory;
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')->withDefault();
    }
    public function expenseDetail()
    {
        return $this->belongsTo(AramiscAddExpense::class, 'payroll_payment_id', 'id');
    }
    public function bankStatementDetail()
    {
        return $this->belongsTo(AramiscBankStatement::class, 'payroll_payment_id', 'id');
    }
    public function paymentMethod()
    {
        return $this->belongsTo(AramiscPaymentMethhod::class, 'payment_mode', 'id')->withDefault();
    }
}
