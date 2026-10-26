<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Module;
use File;

class Localization
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
       
//        if (Storage::exists('.app_installed') and Storage::get('.app_installed')) {
            App::setLocale(getUserLanguage());
            // Une clé par langue, sans Cache::forget à chaque requête : vider la clé partagée à chaque requête faisait
            // qu'une page rendue pendant une requête AJAX simultanée recevait des traductions vides (window._translations = [])
            // → jsLang() plantait et DataTables ne s'initialisait pas. La clé est vidée quand les traductions changent
            // (Localization::forgetTranslations(), appelé par AramiscSystemSettingController).
//            if (Storage::exists('.app_installed') && Storage::get('.app_installed') && DB::connection()->getDatabaseName() != '') {
                if (Schema::hasTable('aramisc_languages')) {
                    Cache::remember(self::cacheKey(App::getLocale()), Carbon::now()->addHours(6), function () {
                        return $this->getTranslations();
                    });
                }
//            }
            try {
                $school_id = 1 ;
                if (auth()->check()) {
                    $school_id = auth()->user()->school_id;
                } elseif (app()->bound('school')) {
                    $school_id = app('school')->id;
                }
                date_default_timezone_set(generalSetting()->timeZone->time_zone ?? 'Asia/Dhaka');
            } catch (\Throwable $th) {
                throw $th;
            }
//        }
        return $next($request);
    }

    /** Clé de cache des traductions JS pour une langue (lue par backEnd/partials/header.blade.php). */
    public static function cacheKey($locale)
    {
        return 'translations_' . $locale;
    }

    /** Vide les traductions mises en cache pour toutes les langues (après ajout / modification de traductions). */
    public static function forgetTranslations()
    {
        Cache::forget('translations');
        $locales = Schema::hasTable('aramisc_languages')
            ? DB::table('aramisc_languages')->pluck('language_universal')->push('en')->unique()
            : collect(['en']);
        foreach ($locales as $locale) {
            Cache::forget(self::cacheKey($locale));
        }
    }

    public function getTranslations()
    {
        $translations = collect();

        $ln = [
            'en',
        ];
        $userLang = getUserLanguage();

        if($userLang != 'en'){
            $ln[] = $userLang;
        }
        foreach ($ln as $locale) {
            $translations[$locale] = [
                'json' => $this->jsonTranslations($locale),
                'php' => $this->phpTranslations($locale),
            ];
        }
        return $translations;
    }


    private function jsonTranslations($locale)
    {
        $files = glob(resource_path('lang/' . $locale . '/*.php'));

        $modules = \Nwidart\Modules\Facades\Module::all();
        foreach ($modules as $module) {
            if (moduleStatusCheck($module->getName())) {
                $module_files = glob(module_path($module->getName()) . '/Resources/lang/' . $locale . '/*.php');
                foreach ($module_files as $module_file) {
                    $files[] = $module_file;
                }
            }
        }

        $lang = [];

        foreach ($files as $file) {
            $file_name = basename($file, '.php');
            if ($file_name !=  'lang' && file_exists($file) && is_array(include($file))) {
                $lang = array_merge($lang, include($file));
            }
        }

        return json_encode($lang, true);

    }

    private function phpTranslations($locale)
    {
        $path = resource_path("lang/$locale");
        if(file_exists($path)){
            return collect(File::allFiles($path))->flatMap(function ($file) use ($locale) {
                $key = ($translation = $file->getBasename('.php'));

                return [$key => trans($translation, [], $locale)];
            });
        }


    }
}
