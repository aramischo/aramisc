<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;

class AramiscPhoneCallLog extends Model
{
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_phone_call_logs';
}
