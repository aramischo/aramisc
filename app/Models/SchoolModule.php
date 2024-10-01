<?php

namespace App\Models;
use App\AramiscSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolModule extends Model
{
    use HasFactory;

    protected $casts = [
        'modules' => 'array',
        'menus' => 'array'
    ];

    public function school()
    {
        return $this->belongsTo(AramiscSchool::class, 'school_id');
    }
}
