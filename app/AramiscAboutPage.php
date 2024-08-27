<?php

namespace App;


use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscAboutPage extends Model
{
    protected static function boot()
    {
        parent::boot();
  
        return static::addGlobalScope(new ActiveStatusSchoolScope);
    }
    use HasFactory;
     // Spécifiez le nom de la table explicitement
     protected $table = 'sm_about_pages';
}
