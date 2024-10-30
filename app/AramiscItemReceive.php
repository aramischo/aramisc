<?php

namespace App;

use App\Scopes\SchoolScope;
use App\AramiscInventoryPayment;
use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscItemReceive extends Model
{
    use HasFactory;
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_item_receives';
    public function suppliers(){
    	return $this->belongsTo('App\AramiscSupplier', 'supplier_id', 'id');
    }

    public function paymentMethodName(){
        return $this->belongsTo('App\AramiscPaymentMethhod','payment_method','id');
    }

    public function bankName(){
        return $this->belongsTo('App\AramiscBankAccount','account_id','id');
    }

    public function itemPayments(){
        return $this->hasMany(AramiscInventoryPayment::class,'item_receive_sell_id','id');
    }

}
