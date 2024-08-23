<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscVisitor extends Model
{

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AcademicSchoolScope);
    }

    use HasFactory;
    public function user()
    {
        return $this->belongsTo('App\User', 'created_by', 'id')->withDefault();
    }
}
