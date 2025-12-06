<?php

namespace App\Http\Controllers\Admin\SystemSettings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\AramiscNotificationSetting;

class AramiscNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
    }

    public function index()
    {
        try {
            $notificationSettings = AramiscNotificationSetting::where('school_id', auth()->user()->school_id)->get();
            if(count($notificationSettings) == 0){
                $olds = AramiscNotificationSetting::where('school_id', 1)->get();
                foreach($olds as $old){
                    $new = new AramiscNotificationSetting(); 
                    $new->event = $old->event;
                    $new->destination = $old->destination;
                    $new->recipient = $old->recipient;
                    $new->subject = $old->subject;
                    $new->template = $old->template;
                    $new->school_id = auth()->user()->school_id;
                    $new->shortcode = $old->shortcode;
                    $new->save();
                }
            }

            return view('backEnd.notification_setting.notification_setting', compact('notificationSettings'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function notificationAddModal()
    {
        try {
            $notificationSettings = AramiscNotificationSetting::where('school_id', auth()->user()->school_id)->get();
            $data = [
                'notificationSettings'=>$notificationSettings,
                'recipients'=>[
                    'Super admin',
                    'Admin',
                    'Student',
                    'Alumni',
                    'Parent',
                    'Teacher'
                ]
            ];
            return view('backEnd.notification_setting.notification_setting_add_modal', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function notificationSettingsAdd(Request $request)
    {
        try {
            $id = $request->id;
            $key = $request->key;
            if (!$id || !$key) {
                Toastr::error('Operation Failed, event or notification not sent', 'Failed');
                return redirect()->back();
            }
            $settings = AramiscNotificationSetting::where('id', $id)
                ->where('school_id', auth()->user()->school_id)
                ->firstOrFail();

            if (!$settings) {
                Toastr::error('Operation Failed, no event found', 'Failed');
                return redirect()->back();
            }

            $recipients = $settings->recipient;
            $subjects = $settings->subject;
            $templates = $settings->template;
            $shortcodes = $settings->shortcode;

            if (array_key_exists($key, $subjects)) {
                Toastr::error('Operation Failed, event already exists', 'Failed');
                return redirect()->back();
            }
            else{
                $subkey = array_key_first($subjects);
                $subjects[$key] = $subjects[$subkey];
                $recipients[$key] = 1;
                $shkey = array_key_first($shortcodes);
                $shortcodes[$key] = $shortcodes[$shkey];

                $temkey = array_key_first($templates);
                $templates[$key]['Email'] = $templates[$temkey]['Email'];
                $templates[$key]['SMS'] = $templates[$temkey]['SMS'] ;
                $templates[$key]['Web'] = $templates[$temkey]['Web'];
                $templates[$key]['App'] =  $templates[$temkey]['App'];

                $settings->recipient = $recipients;
                $settings->subject = $subjects;
                $settings->template = $templates;
                $settings->shortcode = $shortcodes;
                $settings->save();
            }

            return response()->json();

        } catch (\Exception $e) {
            Toastr::error('Operation Failed : '. $e->getMessage(), 'Failed');
            return redirect()->back();
        }
    }

    public function notificationEventModal($id, $key)
    {
        try {
            $eventModal = AramiscNotificationSetting::find($id);
            $data = [];
            $data['id'] = $id;
            $data['key'] = $key;
            $data['shortcode'] = $eventModal->shortcode[$key];
            $data['subject'] = $eventModal->subject[$key];
            $data['emailBody'] = $eventModal->template[$key]['Email'];
            $data['smsBody'] = $eventModal->template[$key]['SMS'];
            $data['appBody'] = $eventModal->template[$key]['App'];
            $data['webBody'] = $eventModal->template[$key]['Web'];

            return view('backEnd.notification_setting.notification_setting_modal', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function notificationSettingsUpdate(Request $request)
    {
        try {
            $id = $request->id;
            $settings = AramiscNotificationSetting::where('id', $id)
                ->where('school_id', auth()->user()->school_id)
                ->firstOrFail();

            if ($request->type == 'destination') {
                $destinations = $settings->destination;
                if (array_key_exists($request->destination, $destinations)) {
                    $destinations[$request->destination] = (int)$request->status;
                }
                $settings->destination = $destinations;
                $settings->save();
            }
            if ($request->type == 'recipient-status') {
                $recipients = $settings->recipient;
                if (array_key_exists($request->recipient, $recipients)) {
                    $recipients[$request->recipient] = (int)$request->status;
                }
                $settings->recipient = $recipients;
                $settings->save();
            }
            if ($request->type == 'recipient') {
                $subjects = $settings->subject;
                if (array_key_exists($request->key, $subjects)) {
                    $subjects[$request->key] = $request->subject;
                }
                $templates = $settings->template;
                if (array_key_exists($request->key, $templates)) {
                    $templates[$request->key]['Email'] = $request->email_body;
                    $templates[$request->key]['SMS'] = $request->sms_body;
                    $templates[$request->key]['Web'] = $request->web_body;
                    $templates[$request->key]['App'] = $request->app_body;
                }
                $settings->subject = $subjects;
                $settings->template = $templates;
                $settings->save();
            }
            return response()->json();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function notificationDeleteModal($id, $key)
    {
        try {
            $eventModal = AramiscNotificationSetting::find($id);
            $data = [];
            $data['id'] = $id;
            $data['key'] = $key;
            $data['shortcode'] = $eventModal->shortcode[$key];
            $data['subject'] = $eventModal->subject[$key];
            $data['emailBody'] = $eventModal->template[$key]['Email'];
            $data['smsBody'] = $eventModal->template[$key]['SMS'];
            $data['appBody'] = $eventModal->template[$key]['App'];
            $data['webBody'] = $eventModal->template[$key]['Web'];

            return view('backEnd.notification_setting.notification_setting_delete_modal', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function notificationSettingsDelete(Request $request)
    {
        try {
            $id = $request->id;
            $key = $request->key;
            $settings = AramiscNotificationSetting::where('id', $id)
                ->where('school_id', auth()->user()->school_id)
                ->firstOrFail();

            $subjects = $settings->subject;
            if (array_key_exists($key, $subjects)) {
                unset($subjects[$key]);
            }
            $templates = $settings->template;
            if (array_key_exists($key, $templates)) {
                unset($templates[$key]);
            }
            $recipients = $settings->recipient;
            if (array_key_exists($key, $recipients)) {
                unset($recipients[$key]);
            }
            $shortcodes = $settings->shortcode;
            if (array_key_exists($key, $shortcodes)) {
                unset($shortcodes[$key]);
            }

            $settings->subject = $subjects;
            $settings->template = $templates;
            $settings->recipient = $recipients;
            $settings->shortcode = $shortcodes;
            $settings->save();

            return response()->json(['id'=>$id,'key'=>$key,'stkey'=>uglifyString($key)]);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function insertdata()
    {
        $datas = AramiscNotificationSetting::all();
        foreach ($datas as $data) {
            $data->delete();
        }
       
    }
}
