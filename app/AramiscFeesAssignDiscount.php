<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;

class AramiscFeesAssignDiscount extends Model
{
    use HasFactory;
	// Spécifiez le nom de la table explicitement
    protected $table = "sm_fees_assign_discounts";
    protected $guarded = ['id'];
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
    
    public function feesDiscount(){
    	return $this->belongsTo('App\AramiscFeesDiscount', 'fees_discount_id', 'id');
    }
}
