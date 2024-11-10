<?php

namespace App\Http\Middleware;

use Closure;

class RouteServe
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Schema::hasTable('aramisc_general_settings') && \Schema::hasTable('users')) {
            $data = \DB::table('aramisc_general_settings')->first();
            if (@$data->system_purchase_code !="") {
                return $next($request);
            } else {
                return view('install.verified_code');
             }
            } else {
                return $next($request);
                //return view('install.verified_code');
            //     return redirect('verified-code');
            //    return view('install.welcome_to_aramisc');
            }
        return $next($request);
    }
}
