<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AramiscHumanDepartment extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_human_departments';
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
}
