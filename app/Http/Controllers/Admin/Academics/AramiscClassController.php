<?php

namespace App\Http\Controllers\Admin\Academics;

use App\AramiscClass;
use App\AramiscSection;
use App\tableList;
use App\YearCheck;
use App\ApiBaseMethod;
use App\AramiscClassSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Academics\ClassRequest;

class AramiscClassController extends Controller
{
    public $date;

    public function __construct()
	{
        $this->middleware('PM');

	}


    public function index(Request $request)
    {
        try {
            $sections = AramiscSection::query();
            if(moduleStatusCheck('University')){
                $data = $sections->where('un_academic_id',getAcademicId());
            }else{
                $data = $sections->where('academic_id',getAcademicId());
            }
            $sections = $data->where('school_id',auth()->user()->school_id)->get();
            $classes = AramiscClass::with('groupclassSections')->withCount('records')->get();
            
    
            return view('backEnd.academics.class', compact('classes', 'sections'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function store(ClassRequest $request)
    {
       // DB::beginTransaction();
            try {
                $class = new AramiscClass();
                $class->class_name = $request->name;
                $class->pass_mark = $request->pass_mark;
                $class->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                $class->created_by=auth()->user()->id;
                $class->school_id = Auth::user()->school_id;
                $class->academic_id = getAcademicId();
                $class->save();
                $class->toArray();

                foreach ($request->section as $section) {
                    $aramiscClassSection = new AramiscClassSection();
                    $aramiscClassSection->class_id = $class->id;
                    $aramiscClassSection->section_id = $section;
                    $aramiscClassSection->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                    $aramiscClassSection->school_id = Auth::user()->school_id;
                    $aramiscClassSection->academic_id = getAcademicId();
                    $aramiscClassSection->save();
                }
                   // DB::commit();
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
           
            } catch (\Exception $e) {
              
               // DB::rollBack();                
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }

    }

    public function edit(Request $request, $id)
    {
        try {
            $classById = AramiscClass::find($id);
            $sectionByNames = AramiscClassSection::select('section_id')->where('class_id', '=', $classById->id)->get();
            $sectionId = array();
            foreach ($sectionByNames as $sectionByName) {
                $sectionId[] = $sectionByName->section_id;
            }

            $sections = AramiscSection::where('active_status', '=', 1)->where('created_at', 'LIKE', '%' . $this->date . '%')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $classes = AramiscClass::where('active_status', '=', 1)->orderBy('id', 'desc')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->withCount('records')->get();
            return view('backEnd.academics.class', compact('classById', 'classes', 'sections', 'sectionId'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(ClassRequest $request)
    {
        AramiscClassSection::where('class_id', $request->id)->delete();
        DB::beginTransaction();

        try {
            $class = AramiscClass::find($request->id);
            $class->class_name = $request->name;
            $class->pass_mark = $request->pass_mark;
            $class->save();
            $class->toArray();
            try {
                foreach ($request->section as $section) {
                    $aramiscClassSection = new AramiscClassSection();
                    $aramiscClassSection->class_id = $class->id;
                    $aramiscClassSection->section_id = $section;
                    $aramiscClassSection->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                    $aramiscClassSection->school_id = Auth::user()->school_id;
                    $aramiscClassSection->academic_id = getAcademicId();
                    $aramiscClassSection->save();
                }

                DB::commit();

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Class has been updated successfully');
                }
                Toastr::success('Operation successful', 'Success');
                return redirect('class');
            } catch (\Exception $e) {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Something went wrong, please try again.');
        }
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
    }

    public function delete(Request $request, $id)
    {
        try {
            $tables = tableList::getTableList('class_id', $id);

            if($tables == null || $tables == "Class sections, ") {
                
                DB::beginTransaction();

                // $class_sections = AramiscClassSection::where('class_id', $id)->get();
                  $class_sections = AramiscClassSection::where('class_id', $id)->get();
                    foreach ($class_sections as $key => $class_section) {
                        AramiscClassSection::destroy($class_section->id);
                    }
                   $section = AramiscClass::destroy($id);
                DB::commit();
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($section) {
                        return ApiBaseMethod::sendResponse(null, 'Class has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                }  
                
                Toastr::success('Operation successful', 'Success');
                return redirect('class');
            } else{
                DB::rollback();
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