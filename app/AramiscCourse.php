<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscCourse extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_courses';
    public function courseCategory()
    {
        return $this->belongsTo('App\AramiscCourseCategory', 'category_id', 'id')->withDefault();
    }
}
