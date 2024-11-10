<?php

namespace App;

use App\AramiscParent;
use App\AramiscStaff;
use App\AramiscStudent;
use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscBook extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_books';
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
    
    
    public function bookCategory()
    {
        return $this->belongsTo('App\AramiscBookCategory', 'book_category_id', 'id')->withDefault();
    }

    public function bookSubject()
    {
        return $this->belongsTo('App\LibrarySubject', 'book_subject_id', 'id')->withDefault();
    }

    public static function getMemberDetails($memberID)
    {

        try {
            return AramiscStudent::select('full_name', 'email', 'mobile')->where('id', '=', $memberID)->first();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function getMemberStaffsDetails($memberID)
    {

        try {
            return AramiscStaff::select('full_name', 'email', 'mobile')->where('user_id', '=', $memberID)->first();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function getParentDetails($memberID)
    {

        try {
            return $getMemberDetails = AramiscParent::select('full_name', 'email', 'mobile')->where('user_id', '=', $memberID)->first();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

}
