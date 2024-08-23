<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscItemSell extends Model
{
    use HasFactory;
	// Spécifiez le nom de la table explicitement
    protected $table = "sm_item_sells";
    protected static function boot (){
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
    
    public function roles(){
    	return $this->belongsTo('Modules\RolePermission\Entities\InfixRole', 'role_id', 'id');
    }

    public function staffDetails(){
    	return $this->belongsTo('App\SmStaff', 'student_staff_id', 'id');
    }

    public function parentsDetails(){
    	return $this->belongsTo('App\SmParent', 'student_staff_id', 'id');
    }

    public function studentDetails(){
    	return $this->belongsTo('App\AramiscStudent', 'student_staff_id', 'id');
    }

    public function paymentMethodName(){
        return $this->belongsTo('App\AramiscPaymentMethhod','payment_method','id');
    }

    public function bankName(){
        return $this->belongsTo('App\SmBankAccount','account_id','id');
    }
}
