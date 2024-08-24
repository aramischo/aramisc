<?php

namespace App;

use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscStudentCategory extends Model
{
    use HasFactory;
	// Spécifiez le nom de la table explicitement
    protected $table = 'sm_student_categorys';
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }

 
    public function students()
    {
        return $this->hasMany(AramiscStudent::class, 'student_category_id', 'id');
    }
}
