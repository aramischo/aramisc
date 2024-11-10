<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscCourseCategory extends Model
{
    protected $guarded = ['id'];
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_course_categories';
    public function courses()
    {
        return $this->hasMany('App\AramiscCourse', 'category_id');
    }
}
