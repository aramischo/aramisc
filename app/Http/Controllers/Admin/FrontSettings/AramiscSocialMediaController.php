<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\ApiBaseMethod;
use App\AramiscSocialMediaIcon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\FrontSettings\SocialMediaRequest;

class AramiscSocialMediaController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('PM');
    }

    public function index()
    {
        $visitors = AramiscSocialMediaIcon::where('school_id', app('school')->id)->get();
        return view('backEnd.frontSettings.socialMedia', compact('visitors'));
    }

    public function store(SocialMediaRequest $request)
    {
        try {
           
            $visitor = new AramiscSocialMediaIcon();
            $visitor->url = $request->url;
            $visitor->icon = $request->icon;
            $visitor->status = $request->status;
            $visitor->school_id = app('school')->id;
            $result = $visitor->save();

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
           
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        try {

            $visitors = AramiscSocialMediaIcon::where('school_id', app('school')->id)->get();
            $visitor = AramiscSocialMediaIcon::where('school_id', app('school')->id)->findOrFail($id);
            return view('backEnd.frontSettings.socialMedia', compact('visitors', 'visitor'));

        } catch (\Exception $e) {
            
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
       
    }

    public function update(SocialMediaRequest $request)
    {
        try {
           
            $visitor = AramiscSocialMediaIcon::where('school_id', app('school')->id)->findOrFail($request->id);
            $visitor->url = $request->url;
            $visitor->icon = $request->icon;
            $visitor->status = $request->status;          
            $result = $visitor->save();


            Toastr::success('Operation successful', 'Success');
            return redirect('social-media');

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function delete($id)
    {
        try {

            AramiscSocialMediaIcon::destroy($id);         
            
            Toastr::success('Operation successful', 'Success');
            return redirect('social-media');

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
