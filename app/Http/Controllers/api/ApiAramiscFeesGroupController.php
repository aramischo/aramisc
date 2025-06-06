<?php

namespace App\Http\Controllers\api;

use App\AramiscClass;
use App\AramiscStudent;
use App\AramiscBaseSetup;
use App\AramiscFeesGroup;
use App\AramiscFeesAssign;
use App\AramiscFeesMaster;
use App\ApiBaseMethod;
use App\AramiscAcademicYear;
use App\AramiscStudentCategory;
use Illuminate\Http\Request;
use App\Scopes\AcademicSchoolScope;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Validator;

class ApiAramiscFeesGroupController extends Controller
{

    public function fees_group_index(Request $request)
    {

        try {
            $fees_groups = AramiscFeesGroup::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_groups, null);
            }

            return view('backEnd.feesCollection.fees_group', compact('fees_groups'));
        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_fees_group_index(Request $request, $school_id)
    {

        try {
            $fees_groups = AramiscFeesGroup::withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', $school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_groups, null);
            }

            return view('backEnd.feesCollection.fees_group', compact('fees_groups'));
        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function fees_group_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required|unique:aramisc_fees_groups|max:200",
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $visitor = new AramiscFeesGroup();
            $visitor->name = $request->name;
            $visitor->description = $request->description;
            $result = $visitor->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Group has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
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
        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_fees_group_store(Request $request, $school_id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required|unique:aramisc_fees_groups|max:200",
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $visitor = new AramiscFeesGroup();
            $visitor->name = $request->name;
            $visitor->description = $request->description;
            $visitor->school_id = $school_id;
            $visitor->academic_id = AramiscAcademicYear::API_ACADEMIC_YEAR($school_id);
            $result = $visitor->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Group has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
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
        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function fees_group_edit(Request $request, $id)
    {

        try {
            $fees_group = AramiscFeesGroup::find($id);
            $fees_groups = AramiscFeesGroup::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees_group'] = $fees_group->toArray();
                $data['fees_groups'] = $fees_groups->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_group', compact('fees_group', 'fees_groups'));
        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_fees_group_edit(Request $request, $school_id, $id)
    {

        try {
            $fees_group = AramiscFeesGroup::withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', $school_id)->find($id);
            $fees_groups = AramiscFeesGroup::withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', $school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees_group'] = $fees_group->toArray();
                $data['fees_groups'] = $fees_groups->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_group', compact('fees_group', 'fees_groups'));
        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function fees_group_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required|max:200|unique:aramisc_fees_groups,name," . $request->id_,
        ]);
    
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        try {
            $visitor = AramiscFeesGroup::withoutGlobalScope(AcademicSchoolScope::class)->find($request->id_);
    
            if (!$visitor) {
                return ApiBaseMethod::sendError('Fees Group not found.');
            }
    
            $visitor->name = $request->name;
            $visitor->description = $request->description;
    
            $result = $visitor->save();
    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Group has been updated successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('fees-group');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    
        
    }
    public function saas_fees_group_update(Request $request, $school_id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required|max:200",

        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $visitor = AramiscFeesGroup::withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', $request->school_id)->find($request->id);
            $visitor->name = $request->name;
            $visitor->description = $request->description;
            $visitor->school_id = $school_id;
            $result = $visitor->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Group has been updated successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('fees-group');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
                }
        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function fees_group_delete(Request $request)
    {

        try {
            $fees_group = AramiscFeesGroup::destroy($request->id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($fees_group) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Group has been deleted successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($fees_group) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('fees-group');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect('fees-group');
                }
            }
        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
    public function saas_fees_group_delete(Request $request, $school_id)
    {

        try {
            $fees_group = AramiscFeesGroup::withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', $school_id)->where('id', $request->id)->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($fees_group) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Group has been deleted successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($fees_group) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('fees-group');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect('fees-group');
                }
            }
        } catch (\Exception$e) {
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }
}
