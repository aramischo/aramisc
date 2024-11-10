<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscAmountTransfer extends Model
{
    use HasFactory;
     // Spécifiez le nom de la table explicitement
     protected $table = 'aramisc_amount_transfers';
    public function fromPaymentMethodName(){
        return $this->belongsTo('App\AramiscPaymentMethhod','from_payment_method','id');
    }

    public function toPaymentMethodName(){
        return $this->belongsTo('App\AramiscPaymentMethhod','to_payment_method','id');
    }
}
