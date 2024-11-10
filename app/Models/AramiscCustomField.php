<?php

namespace App\Models;

use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscCustomField extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_custom_fields';

    protected static function boot()
    {
        parent::boot();
  
        return static::addGlobalScope(new SchoolScope);
    }
}
