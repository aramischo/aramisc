<?php

namespace App\Http\Controllers\Admin\FeesCollection;

use App\AramiscFeesType;
use App\AramiscFeesGroup;
use App\AramiscFeesMaster;
use App\ApiBaseMethod;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\FeesCollection\AramiscFeesGroupRequest;

class AramiscFeesGroupController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
	}

    public function index(Request $request)
    {

        try{
             $fees_groups = AramiscFeesGroup::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_groups, null);
            }

            return view('backEnd.feesCollection.fees_group', compact('fees_groups'));
            
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }
    public function store(AramiscFeesGroupRequest $request)
    {

        // if ($validator->fails()) {
        //     if (ApiBaseMethod::checkUrl($request->fullUrl())) {
        //         return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        //     }
            
        // }

        try{
            $fees_group = new AramiscFeesGroup();
            $fees_group->name = $request->name;
            $fees_group->description = $request->description;
            $fees_group->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $fees_group->un_academic_id = getAcademicId();
            }else {
                $fees_group->academic_id = getAcademicId();
            }
            $result = $fees_group->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Group has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } 
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }

    public function edit(Request $request, $id)
    {

        try{
         
            $fees_group = AramiscFeesGroup::find($id);
            $fees_groups = AramiscFeesGroup::where('school_id',Auth::user()->school_id)->where('academic_id', getAcademicId())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees_group'] = $fees_group->toArray();
                $data['fees_groups'] = $fees_groups->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_group', compact('fees_group', 'fees_groups'));
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }
    public function update(AramiscFeesGroupRequest $request)
    {
        try{
           
            $fees_group = AramiscFeesGroup::find($request->id);
            $fees_group->name = $request->name;
            $fees_group->description = $request->description;
            if(moduleStatusCheck('University')){
                $fees_group->un_academic_id = getAcademicId();
            }else {
                $fees_group->academic_id = getAcademicId();
            }
            $result = $fees_group->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Group has been updated successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } 

            Toastr::success('Operation successful', 'Success');
            return redirect('fees-group');

        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }


    public function deleteGroup(Request $request)
    {

            try {
                $tables = \App\tableList::getTableList('fees_group_id', $request->id);
                if ($tables==null) { 

                         $fees_group = AramiscFeesGroup::destroy($request->id);                  
                 
                        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                            if ($fees_group) {
                                return ApiBaseMethod::sendResponse(null, 'Fees Group has been deleted successfully');
                            } else {
                                return ApiBaseMethod::sendError('Something went wrong, please try again');
                            }
                        } 
          
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();

                } else {
                    $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }

            } catch (\Illuminate\Database\QueryException $e) {

                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error($msg, 'Failed');
                return redirect()->back();
           
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }

    }

}