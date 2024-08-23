<?php
namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscCourseCategory extends Model
{
    protected $guarded = ['id'];
    use HasFactory;

    // Specify the table name explicitly
    protected $table = "sm_course_categories";

    public function courses()
    {
        return $this->hasMany('App\AramiscCourse', 'category_id');
    }
}
