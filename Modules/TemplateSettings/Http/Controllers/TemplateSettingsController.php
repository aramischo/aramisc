<?php

namespace Modules\TemplateSettings\Http\Controllers;

use App\User;
use App\AramiscUserLog;
use App\AramiscTemplate;
use App\AramiscGeneralSettings;
use App\InfixModuleManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

class TemplateSettingsController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            return view('templatesettings::index');
        }catch(\Exception $e) {
            Log::info($e->getMessage());
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function about()
    {

        try {
            $data = \App\InfixModuleManager::where('name', 'TemplateSettings')->first();
            return view('templatesettings::index', compact('data'));
        }catch(\Exception $e) {
            Log::info($e->getMessage());
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function emailTemplate()
    {
        $emailTempletes = AramiscTemplate::where('type', 'email')->where('school_id', auth()->user()->school_id)->get();

        return view('templatesettings::emailTemplate', compact('emailTempletes'));
    }

    public function emailTemplateUpdate(Request $request)
    {
        $request->validate([
            'subject' => 'required',
            'body' => 'required'
        ]);

        try {
            $updateData = AramiscTemplate::find($request->id);
            $updateData->type = "email";
            $updateData->subject = $request->subject;
            $updateData->body = $request->body;
            $updateData->status = ($request->status)? $request->status: 0;
            $updateData->school_id = Auth::user()->school_id;
            $updateData->update();
            Toastr::success('Operation success', 'Success');
            return redirect()->route('templatesettings.email-template');
        }catch(\Exception $e){
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function smsTemplate()
    {
        try {
            $smsTemplates = AramiscTemplate::where('type','sms')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            return view('templatesettings::smsTemplate', compact('smsTemplates'));
        }catch(\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function smsTemplateUpdate(Request $request){
        try {
            $updateData = AramiscTemplate::find($request->id);
            $updateData->type = 'sms';
            $updateData->body = $request->body;
            $updateData->status = $request->status? 1 : 0;
            $updateData->school_id = Auth::user()->school_id;
            $updateData->update();
            Toastr::success('Operation success', 'Success');
            return redirect()->route('templatesettings.sms-template');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}