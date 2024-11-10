<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageMiddleware
{
    public function handle($request, Closure $next)
    {
        // Vérifie si une langue est définie dans la session
        if (Session::has('locale')) {
            // Applique la langue stockée dans la session
            App::setLocale(Session::get('locale'));
        }

        return $next($request);
    }
}
