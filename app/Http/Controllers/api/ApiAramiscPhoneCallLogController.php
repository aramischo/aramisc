<?php

namespace App\Http\Controllers\api;

use Validator;
use App\YearCheck;
use App\ApiBaseMethod;
use App\AramiscPhoneCallLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;

class ApiAramiscPhoneCallLogController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('PM');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        try{
            $phone_call_logs = AramiscPhoneCallLog::where('academic_id', getAcademicId())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($phone_call_logs, null);
            }
            return view('backEnd.admin.phone_call', compact('phone_call_logs'));
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back(); 
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'phone' => "required|regex:/^([0-9\s\-\+\(\)]*)$/|",
            'name' => "sometimes|nullable|max:120",
            'call_duration' => "sometimes|nullable|max:30",
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try{
            $phone_call_log = new AramiscPhoneCallLog();
            $phone_call_log->name = $request->name;
            $phone_call_log->phone = $request->phone;
            $phone_call_log->date = date('Y-m-d', strtotime($request->date));
            $phone_call_log->description = $request->description;
            $phone_call_log->next_follow_up_date = date('Y-m-d', strtotime($request->follow_up_date));
            $phone_call_log->call_duration = $request->call_duration;
            $phone_call_log->call_type = $request->call_type;
            $result = $phone_call_log->save();
    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Vehicle has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back(); 
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
       
        try{
            $phone_call_logs = AramiscPhoneCallLog::where('academic_id', getAcademicId())->get();
            $phone_call_log = AramiscPhoneCallLog::find($id);
    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['phone_call_logs'] = $phone_call_logs->toArray();
                $data['phone_call_log'] = $phone_call_log->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
    
            return view('backEnd.admin.phone_call', compact('phone_call_logs', 'phone_call_log'));
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back(); 
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'phone' => "required|regex:/^([0-9\s\-\+\(\)]*)$/|",
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }        
        try{
            $phone_call_log = AramiscPhoneCallLog::find($request->id);
            $phone_call_log->name = $request->name;
            $phone_call_log->phone = $request->phone;
            $phone_call_log->date = date('Y-m-d', strtotime($request->date));
            $phone_call_log->description = $request->description;
            $phone_call_log->next_follow_up_date = date('Y-m-d', strtotime($request->follow_up_date));
            $phone_call_log->call_duration = $request->call_duration;
            $phone_call_log->call_type = $request->call_type;
            $result = $phone_call_log->save();
    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Call Log has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('phone-call');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back(); 
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        
        try{
            $result = AramiscPhoneCallLog::destroy($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Call Log has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('phone-call');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back(); 
        }
    }
}