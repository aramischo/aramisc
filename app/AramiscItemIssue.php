<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscItemIssue extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_item_issues';
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
    
    public function items(){
    	return $this->belongsTo('App\AramiscItem', 'item_id', 'id');
    }

    public function categories(){
    	return $this->belongsTo('App\AramiscItemCategory', 'item_category_id', 'id');
    }
    
}
