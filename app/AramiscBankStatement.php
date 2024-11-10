<?php

namespace App;

use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscBankStatement extends Model
{
    use HasFactory;
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_bank_statements';
    public function bankName()
    {
        return $this->belongsTo('App\AramiscBankAccount', 'bank_id', 'id');
    }

    public function paymentMethod(){
        return $this->belongsTo('App\AramiscPaymentMethhod','payment_method','id');
    }
    
}
