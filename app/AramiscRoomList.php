<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscRoomList extends Model
{
    use HasFactory;
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
   // Spécifiez le nom de la table explicitement
   protected $table = 'aramisc_room_lists';
    public function dormitory()
    {
        return $this->belongsTo('App\AramiscDormitoryList', 'dormitory_id');
    }

    public function roomType()
    {
        return $this->belongsTo('App\AramiscRoomType', 'room_type_id');
    }
}
