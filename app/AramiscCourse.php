<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscCourse extends Model
{
    use HasFactory;

    // Specify the table name explicitly
    protected $table = 'sm_courses';

    public function courseCategory()
    {
        return $this->belongsTo('App\AramiscCourseCategory', 'category_id', 'id')->withDefault();
    }
}
