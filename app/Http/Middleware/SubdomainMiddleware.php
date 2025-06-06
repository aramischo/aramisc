<?php

namespace App\Http\Middleware;

use App\AramiscCustomLink;
use App\AramiscFrontendPersmission;
use App\AramiscGeneralSettings;
use App\AramiscHeaderMenuManager;
use App\AramiscSocialMediaIcon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Spatie\Valuestore\Valuestore;

class SubdomainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $school = SaasSchool();
        // dd($school);
        Session::put('domain', $school->domain);
        app()->forgetInstance('school');
        app()->instance('school', $school);
        $settings_prefix = Str::lower(str_replace(' ', '_', $school->domain));
        $chat_settings = storage_path('app/chat/' . $settings_prefix . '_settings.json');
        if (!file_exists($chat_settings)) {
            copy(storage_path('app/chat/default_settings.json'), $chat_settings);
        }

        app()->scoped('general_settings', function () use ($chat_settings) {
            return Valuestore::make($chat_settings);
        });

        view()->composer('frontEnd.home.front_master', function ($view) use ($school) {

            if(activeTheme() && activeTheme() == 'edulia'){
                $menus = AramiscHeaderMenuManager::when(activeTheme(), function($q){ $q->where('theme', activeTheme());})->when(activeTheme() == NULL, function($q){ $q->where('theme', 'default');})->whereNull('parent_id')->where('school_id', app('school')->id)->orderBy('position')->get();
            }else{
                $menus = AramiscHeaderMenuManager::where('theme', 'default')->whereNull('parent_id')->where('school_id', app('school')->id)->orderBy('position')->get();
            }

            $data = [
                'social_permission' => AramiscFrontendPersmission::where('name', 'Social Icons')->where('parent_id', 1)->where('is_published', 1)->where('school_id', app('school')->id)->first(),
                'menus' => $menus,
                'custom_link' => AramiscCustomLink::where('school_id', app('school')->id)->first(),
                'social_icons' => AramiscSocialMediaIcon::where('school_id', app('school')->id)->where('status', 1)->get(),
                'school' => $school,
            ];

            $view->with($data);

        });

        return $next($request);
    }
}
