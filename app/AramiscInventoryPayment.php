<?php

namespace App;

use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscInventoryPayment extends Model
{
    use HasFactory;
     // Spécifiez le nom de la table explicitement
     protected $table = 'aramisc_inventory_payments';
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }
    
    public function paymentMethods(){
    	return $this->belongsTo('App\AramiscPaymentMethhod', 'payment_method', 'id');
    }

   
    public  static function itemPaymentdetails($item_receive_id){
    	
        try {
            $itemPaymentdetails = AramiscInventoryPayment::where('item_receive_sell_id', '=', $item_receive_id)->get();
            return count($itemPaymentdetails);
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }
}
