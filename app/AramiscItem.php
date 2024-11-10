<?php

namespace App;

use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscItem extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_items';
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }

    
    public function category()
    {
        return $this->belongsTo('App\AramiscItemCategory', 'item_category_id', 'id');
    }
}
