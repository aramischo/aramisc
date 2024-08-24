<?php

namespace App\Models;

use App\AramiscNews;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AramiscNewsComment extends Model
{
    use HasFactory;
	// Specify the table name explicitly
    protected $table = 'sm_news_comments';
     
    public function onlyChildrenFrontend()
    {
        return $this->hasMany(AramiscNewsComment::class, 'parent_id', 'id')
                    ->with(['onlyChildrenFrontend'])->where('status', 1);
    }

    public function onlyChildrenBackend()
    {
        return $this->hasMany(AramiscNewsComment::class, 'parent_id', 'id')->with(['onlyChildrenBackend']);
    }

    public function news()
    {
        return $this->belongsTo(AramiscNews::class, 'news_id', 'id')->withDefault('');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withDefault('');
    }

    public function getCountApproveCommentAttribute()
    {
        return AramiscNewsComment::where('news_id', $this->news_id)->where('status', 1)->count();
    }

    public function getCountUnaproveCommentAttribute()
    {
        return AramiscNewsComment::where('news_id', $this->news_id)->where('status', 0)->count();
    }

    public function reply_to($parentId)
    {
        $commentData = AramiscNewsComment::with('user')->find($parentId);
        return $commentData->user->full_name;
    }
}
