<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscAssignVehicle extends Model
{
    // protected static function boot()
    // {
    //     parent::boot();
  
    //     static::addGlobalScope(new ActiveStatusSchoolScope);
    // }
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_assign_vehicles';
    public function route(){
    	return $this->belongsTo('App\AramiscRoute', 'route_id', 'id');
    }
    public function vehicle(){
    	return $this->belongsTo('App\AramiscVehicle', 'vehicle_id', 'id');
    }
}
