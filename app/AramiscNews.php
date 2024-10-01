<?php

namespace App;

use App\Models\AramiscNewsComment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscNews extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_news';
    public function category()
    {
        return $this->belongsTo('App\AramiscNewsCategory');
    }

    public function newsComments()
    {
        return $this->hasMany(AramiscNewsComment::class, 'news_id')->whereNull('parent_id')->where('status', 1);
    }
    

    public function scopeMissions($q)
    {
        return $q->whereHas('category', function($q){
            
            return $q->where('type', 'mission');
         
        });
    }

    public function scopeHistories($q)
    {
        return $q->whereHas('category', function($q){
            
            return $q->where('type', 'history');
         
        });
    }
}
