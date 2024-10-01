<?php

namespace Modules\DownloadCenter\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VideoUpload extends Model
{
    use HasFactory;

    protected $fillable = [];

    public function class()
    {
        return $this->belongsTo('App\AramiscClass', 'class_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo('App\AramiscSection', 'section_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }
}
