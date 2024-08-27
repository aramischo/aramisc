<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AramiscNotification extends Model
{
    use HasFactory;
    // Spécifiez le nom de la table explicitement
    protected $table = 'sm_notifications';
    public static function notifications()
    {
        $user = Auth()->user();
        if ($user) {
            return $user->allNotifications->where('user_id', $user->id)->where('role_id', $user->role_id)->where('is_read', 0);
        }

    }
}
