<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmBookIssue extends Model
{
    use HasFactory;
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
    public function books()
    {
        return $this->belongsTo('App\SmBook', 'book_id', 'id');
    }

    public function member()
    {
        return $this->belongsTo(SmLibraryMember::class, 'member_id', 'student_staff_id');
    }

    public function user()
	{
	  return $this->belongsTo('App\Models\User', 'member_id', 'id');
	}

    public function getMemberDetailsAttribute()
    {
        $full_name = '';
        if ($this->member) {
            $full_name = $this->member->aramiscStudentDetails->full_name;
        }elseif($this->member && $this->member->member_type == 3){
            $full_name = $this->member->parentsDetails->guardians_name;
        } else {
            $full_name = @$this->member->staffDetails->full_name;
        }

        return $full_name;
    }

}
