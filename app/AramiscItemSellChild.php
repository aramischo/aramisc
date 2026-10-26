<?php

namespace App;

use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscItemSellChild extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'aramisc_item_sell_children'; // « _childs » n'existe pas en base
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }

    
    public function items()
    {
        return $this->belongsTo('App\AramiscItem', 'item_id', 'id');
    }
}
