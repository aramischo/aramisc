<?php

namespace App\Http\Controllers\Admin\SystemSettings;

use App\AramiscClass;
use App\AramiscSection;
use App\tableList;
use App\YearCheck;
use App\ApiBaseMethod;
use App\AramiscAcademicYear;
use App\AramiscClassSection;
use App\AramiscGeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Scopes\AcademicSchoolScope;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isNull;
use App\Scopes\ActiveStatusSchoolScope;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\GeneralSettings\AramiscAcademicYearRequest;

class AramiscAcademicYearController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }
    
    public function index(Request $request)
    {
        try {
            $academic_years = AramiscAcademicYear::where('active_status', 1)->orderBy('year', 'ASC')->where('school_id', Auth::user()->school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($academic_years, null);
            }
            return view('backEnd.systemSettings.academic_year', compact('academic_years'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function store(AramiscAcademicYearRequest $request)
    {
        $yr = AramiscAcademicYear::orderBy('id', 'desc')->where('school_id', Auth::user()->school_id)->first();
        $created_year = $request->starting_date;
       
        DB::beginTransaction();
        $academic_year = new AramiscAcademicYear();
        $academic_year->year = $request->year;
        $academic_year->title = $request->title;
        $academic_year->starting_date = date('Y-m-d', strtotime($request->starting_date));
        $academic_year->ending_date = date('Y-m-d', strtotime($request->ending_date));
        if ($request->copy_with_academic_year != null) {
                $academic_year->copy_with_academic_year =implode(",",$request->copy_with_academic_year);
            }
        $academic_year->created_at = $created_year;
        $academic_year->school_id = Auth::user()->school_id;
        $result = $academic_year->save();
        if($result){
            session()->forget('academic_years');
            $academic_years = AramiscAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            session()->put('academic_years',$academic_years);   
        }
        $aramisc_Gs = AramiscGeneralSettings::where('school_id', Auth::user()->school_id)->first();
        $aramisc_Gs->session_id = $academic_year->id;
        $aramisc_Gs->academic_id = $academic_year->id;
        $aramisc_Gs->session_year = $academic_year->year;
        $aramisc_Gs->save();
        session()->forget('sessionId'); 
        session()->put('sessionId', $aramisc_Gs->session_id); 
        session()->forget('generalSetting');
        $generalSetting = AramiscGeneralSettings::where('school_id',Auth::user()->school_id)->first();
        session()->put('generalSetting', $generalSetting);

        $data = \App\AramiscMarksGrade::where('academic_id', $yr->id)->where('school_id', Auth::user()->school_id)->get();
      
        if (!empty($data)) {
            foreach ($data as $k0ey => $value) {
                $newClient = $value->replicate();
                $newClient->created_at = $created_year;
                $newClient->updated_at = $created_year;
                $newClient->academic_id = $academic_year->id;
                $newClient->save();
            }
        }

        if ($request->copy_with_academic_year != null) {
            $tables = $request->copy_with_academic_year;
            $tables = array_filter($tables);
            if (!empty($tables)) {
                if ($yr) {
                    foreach ($tables as $table_name) {
                        $data = $table_name::where('academic_id', $yr->id)->where('school_id', Auth::user()->school_id)->withoutGlobalScopes([
                            StatusAcademicSchoolScope::class,
                            AcademicSchoolScope::class,
                            ActiveStatusSchoolScope::class
                        ])->get();
                       
                        if (!empty($data)) {
                            foreach ($data as $k0ey => $value) {
                                $newClient = $value->replicate();
                                $newClient->created_at = $created_year;
                                $newClient->updated_at = $created_year;
                                $newClient->academic_id = $academic_year->id;
                                $newClient->save();
                            }
                        }
                    }
                }
                $classes = AramiscClass::where('academic_id', $academic_year->id)->where('school_id', Auth::user()->school_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
                $sections = AramiscSection::where('academic_id', $academic_year->id)->where('school_id', Auth::user()->school_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
                foreach ($classes as $class) {
                    foreach ($sections as $section) {
                        $class_section = new AramiscClassSection();
                        $class_section->class_id = $class->id;
                        $class_section->section_id = $section->id;
                        $class_section->created_at = $created_year;
                        $class_section->school_id = Auth::user()->school_id;
                        $class_section->academic_id = $academic_year->id;
                        $class_section->save();
                    }
                }
            }
        }
        


        DB::commit();
        Toastr::success('Operation successful', 'Success');
        return redirect()->back();
    }

    public function show(Request $request, $id)
    {
        try {
             if (checkAdmin()) {
                $academic_year = AramiscAcademicYear::find($id);
            }else{
                $academic_year = AramiscAcademicYear::where('id',$id)->where('school_id',Auth::user()->school_id)->first();
            }
            $academic_years = AramiscAcademicYear::where('school_id', Auth::user()->school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['academic_year'] = $academic_year->toArray();
                $data['academic_years'] = $academic_years->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.systemSettings.academic_year', compact('academic_year', 'academic_years'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function edit($id)
    {
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
    }


    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'year' => 'required|numeric|digits:4',
            'title' => "required|max:150",
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $yr = AramiscAcademicYear::where('id', getAcademicId())->where('school_id', Auth::user()->school_id)->first();
            $created_year = $request->starting_date;
            if ($yr->year == $request->year) {
                Toastr::warning('You cannot copy current academic year info.', 'Warning');
                return redirect('academic-year');
            }

            if (checkAdmin()) {
                $academic_year = AramiscAcademicYear::find($request->id);
            }else{
                $academic_year = AramiscAcademicYear::where('id',$request->id)->where('school_id',Auth::user()->school_id)->first();
            }
            $academic_year->year = $request->year;
            $academic_year->title = $request->title;
            $academic_year->starting_date = date('Y-m-d', strtotime($request->starting_date));
            $academic_year->ending_date = date('Y-m-d', strtotime($request->ending_date));
            $academic_year->created_at = $created_year;
            if ($yr->year != $request->year) {
                if ($request->copy_with_academic_year != null) {
                    $academic_year->copy_with_academic_year =implode(",",$request->copy_with_academic_year);
                }
            }
            $result = $academic_year->save();
            if($result){
                session()->forget('academic_years');
                $academic_years = AramiscAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
                session()->put('academic_years',$academic_years);
            }
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Year has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('academic-year');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            // $session_id = 'academic_id';
            // $tables = tableList::getTableList($session_id, $id);
            try {

                if (getAcademicId() != $id) {
                    if (checkAdmin()) {
                        $delete_query = AramiscAcademicYear::find($id);
                    }else{
                        $delete_query = AramiscAcademicYear::where('id',$id)->where('school_id',Auth::user()->school_id)->first();
                    }


                    $del_tables=explode(',',@$delete_query->copy_with_academic_year);
               

                    if(!is_null($del_tables)){
                        foreach ($del_tables as $del_table_name) {
                            if($del_table_name){
                                $del_data = new $del_table_name();
                                $del_data = $del_data->where('academic_id', $id)->delete();
                            }
                        }
                    }

                    AramiscClassSection::where('academic_id', $request->id)->where('school_id', Auth::user()->school_id)->delete();
                  
                    $delete_query->delete();

                   
                    if ($delete_query) {
                        session()->forget('academic_years');
                        $academic_years = AramiscAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
                        session()->put('academic_years', $academic_years);
                        
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                    
                } else {
                    Toastr::warning('You cannot delete current academic year.', 'Warning');
                    return redirect()->back();
                }
                
                
            } catch (\Illuminate\Database\QueryException $e) {
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}