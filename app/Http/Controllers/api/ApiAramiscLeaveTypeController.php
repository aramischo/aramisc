<?php

namespace App\Http\Controllers\api;

use Validator;
use App\AramiscLeaveType;
use App\ApiBaseMethod;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;

class ApiAramiscLeaveTypeController extends Controller
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
        
            $leave_types = AramiscLeaveType::get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {    
                return ApiBaseMethod::sendResponse($leave_types->toArray(), null);
            }
            return view('backEnd.humanResource.leave_type', compact('leave_types'));
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
            'type' => "required|unique:aramisc_leave_types",
           
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
            $leave_type = new AramiscLeaveType();
            $leave_type->type = $request->type;
            $leave_type->total_days = $request->total_days;
            $result = $leave_type->save();
    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Type has been created successfully');
                }
                return ApiBaseMethod::sendError('Something went wrong, please try again.');
            } else {
                if ($result) {
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
                    // return redirect()->back()->with('message-success', 'Type has been created successfully');
                }
    
                return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
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
            $leave_type = AramiscLeaveType::find($id);
            $leave_types = AramiscLeaveType::get();
    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['leave_type'] = $leave_type->toArray();
                $data['leave_types'] = $leave_types->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.humanResource.leave_type', compact('leave_types', 'leave_type'));
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
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'type' => "required|unique:aramisc_leave_types,type," . $request->id,
                'id' => "required"
            ]);
        } else {
            $validator = Validator::make($input, [
                'type' => "required|unique:aramisc_leave_types,type," . $request->id
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        
        try{
            $leave_type = AramiscLeaveType::find($request->id);
            $leave_type->type = $request->type;
            $leave_type->total_days = $request->total_days;
            $result = $leave_type->save();
    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Type has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    return redirect('leave-type')->with('message-success', 'Type has been updated successfully');
                } else {
                    return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
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
        $tables = \App\tableList::getTableList('type_id',$id);
        try {
            $leave_type = AramiscLeaveType::destroy($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($leave_type) {
                    return ApiBaseMethod::sendResponse(null, 'Type has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($leave_type) {
                    return redirect()->back()->with('message-success-delete', 'Type has been deleted successfully');
                } else {
                    return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {

            $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
            Toastr::error('This item already used', 'Failed');
            return redirect()->back();
        }
     } catch (\Exception $e) {
            return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
        }
    }
}
