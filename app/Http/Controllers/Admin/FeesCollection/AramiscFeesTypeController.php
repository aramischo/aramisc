<?php

namespace App\Http\Controllers\Admin\FeesCollection;
use App\tableList;
use App\YearCheck;
use App\AramiscFeesType;
use App\AramiscFeesGroup;
use App\AramiscFeesMaster;
use App\ApiBaseMethod;
use App\AramiscFeesPayment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\FeesCollection\AramiscFeesTypeRequest;

class AramiscFeesTypeController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
	}

    public function index(Request $request)
    {

        try{
            $fees_types = AramiscFeesType::with('fessGroup')->where('school_id', Auth::user()->school_id)->get();
            $fees_groups = AramiscFeesGroup::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.feesCollection.fees_type', compact('fees_types','fees_groups'));
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }
    public function store(AramiscFeesTypeRequest $request)
    {
        try{
            $fees_type = new AramiscFeesType();
            $fees_type->name = $request->name;
            $fees_type->fees_group_id = $request->fees_group;
            $fees_type->description = $request->description;
            $fees_type->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $fees_type->un_academic_id = getAcademicId();
            }else {
                $fees_type->academic_id = getAcademicId();
            }
            $result = $fees_type->save();

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
            $fees_type = AramiscFeesType::where('school_id', Auth::user()->school_id)->find($id);
            $fees_types = AramiscFeesType::where('school_id', Auth::user()->school_id)->get();
            $fees_groups = AramiscFeesGroup::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.feesCollection.fees_type', compact('fees_type', 'fees_types','fees_groups'));
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }
    public function update(Request $request)
    {

        // if ($validator->fails()) {
        //     if (ApiBaseMethod::checkUrl($request->fullUrl())) {
        //         return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        //     }

        // }

        try{
            $fees_type = AramiscFeesType::find($request->id);
            $fees_type->name = $request->name;
            $fees_type->fees_group_id = $request->fees_group;
            $fees_type->description = $request->description;
            $result = $fees_type->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees type has been updated successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } 
            Toastr::success('Operation successful', 'Success');
            return redirect()->route('fees_type');
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }
    public function delete(Request $request, $id)
    {
       try{
        $id_key = 'fees_type_id';

        $tables = tableList::getTableList($id_key,$id);

        try {
            if ($tables==null) {
                $check_fees_type_in_master=AramiscFeesMaster::where('fees_type_id',$id)->first();
                $check_fees_type_in_payment=AramiscFeesPayment::where('active_status',1)->where('fees_type_id',$id)->first();
                if ($check_fees_type_in_master!=null && $check_fees_type_in_payment!=null) {
                    Toastr::warning('Operation Failed', 'Used Data');
                    return redirect('fees-type');
                }

                $delete_query = AramiscFeesType::destroy($id);

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Fees Type has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
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
          }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}