<?php

namespace App;

use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscStudentCategory extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }

   // Spécifiez le nom de la table explicitement
   protected $table = 'ssm_student_categories';
    public function students()
    {
        return $this->hasMany(AramiscStudent::class, 'student_category_id', 'id');
    }
}
