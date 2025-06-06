<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class AramiscChartOfAccount extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_chart_of_accounts';
}
