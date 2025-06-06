<?php
namespace App;

use App\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;

class AramiscStudentIdCard extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
   protected $table = 'aramisc_student_id_cards';
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
    
    public static function roleName($id){
        $id_card= AramiscStudentIdCard::find($id);
            $arr=[];
            $names=[];
            $value=json_decode($id_card->role_id,true);
            foreach($value as $values){
                $arr[] = $values;
            }
            $roleNames = Role::whereIn('id',$arr)->get(['id','name']);
        return $roleNames;
    }

    public function scopeStatus($query){
        return $query->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id);
    }

    public static function studentName($parent_id){
        $studentInfos = AramiscStudent::where('parent_id',$parent_id)
                    ->where('active_status',1)
                    ->where('school_id', Auth::user()->school_id)
                    ->get(['full_name','student_photo']);
        return $studentInfos;
    }
}
