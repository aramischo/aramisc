<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscRoomType extends Model
{
    use HasFactory;   
    protected $casts = [
        'id' => 'integer',
        'type' => 'string',
    ];


    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
    // Spécifiez le nom de la table explicitement
   protected $table = 'sm_room_types';
}
