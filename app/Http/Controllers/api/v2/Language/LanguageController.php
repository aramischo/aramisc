<?php

namespace App\Http\Controllers\api\v2\Language;

use App\Language;
use App\AramiscLanguage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LanguageController extends Controller
{
    public function allList(Request $request)
    {
        $languages = AramiscLanguage::orderBy('id', 'ASC')
            ->get()->map(function ($language) use ($request) {
                $active = $request->user() ? ($request->user()->language == $language->language_universal) : boolval($language->active_status);
                $rtl = false;
                if($active){
                    $lang = Language::find($language->lang_id);
                    if($lang && $lang->rtl){
                        $rtl = true;
                    }
                }
                return [
                    'id' => $language->id,
                    'locale' => $active ? 'active' : $language->language_universal,
                    'default_locale' => $language->language_universal,
                    'lang_name' => $language->native,
                    'rtl' => $rtl,
                    'active_status' => $active,
                ];
            });

        $data = [];
        $activeLanguage = $languages->where('active_status', 1)->first();

        $langName = $activeLanguage ? $activeLanguage['default_locale'] : 'en';
        $en = $active = __('mobile_app', [], 'en');
        if ($langName != 'en') {
            $active = __('mobile_app', [], $langName);

            if (count($en) != count($active)) {
                // L'anglais ne sert qu'à combler les clés manquantes, il ne doit pas écraser la traduction
                $active = array_merge($en, $active);
            }
        }

        $data['lang_list'] = $languages;
        $data['lang'] =  $active;
        // Couleurs de l'application, réglées dans Réglages généraux
        $data['mobile_colors'] = \App\AramiscGeneralSettings::mobileColors(optional(auth()->user())->school_id ?? app('school')->id);

        return response()->json($data);
    }

    public function myLanguages(Request $request)
    {
        $this->validate($request, [
            'lang_id' => 'required'
        ]);

        $languageId = AramiscLanguage::where('school_id', auth()->user()->school_id)->find($request->lang_id);
        $user = auth()->user();
        if ($languageId) {
            $user->language = $languageId->language_universal;
            $user->save();
        }


        $languages = AramiscLanguage::where('school_id', auth()->user()->school_id)
            ->get()->map(function ($language) use ($user) {
                $active = $user->language == $language->language_universal;
                $rtl = false;
                if($active){
                    $lang = Language::find($language->lang_id);
                    if($lang && $lang->rtl){
                        $rtl = true;
                    }
                }
                return [
                    'id' => $language->id,
                    'locale' => $active ? 'active': $language->language_universal,
                    'default_locale' => $language->language_universal,
                    'lang_name' => $language->native,
                    'rtl' => $rtl,
                    'active_status' => $active,
                ];
            });

        $data = [];
        $activeLanguage = $languages->where('active_status', 1)->first();

        $langName = $activeLanguage ? $activeLanguage['default_locale'] : 'en';
        $en = $active = __('mobile_app', [], 'en');
        if ($langName != 'en') {
            $active = __('mobile_app', [], $langName);

            if (count($en) != count($active)) {
                // L'anglais ne sert qu'à combler les clés manquantes, il ne doit pas écraser la traduction
                $active = array_merge($en, $active);
            }
        }

        $data['lang_list'] = $languages;
        $data['lang'] =  $active;
        // Couleurs de l'application, réglées dans Réglages généraux
        $data['mobile_colors'] = \App\AramiscGeneralSettings::mobileColors(optional(auth()->user())->school_id ?? app('school')->id);

        return response()->json($data);
    }
}
