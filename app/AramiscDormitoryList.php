<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class AramiscDormitoryList extends Model
{
    use HasFactory;
// Spécifiez le nom de la table explicitement
    protected $table = "sm_exam_dormitory_lists";
    protected $casts = [
        'id' => 'integer',
        'dormitory_name' => 'string',
    ];

    
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new ActiveStatusSchoolScope);
    } 
    
}
