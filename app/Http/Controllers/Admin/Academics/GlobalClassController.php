<?php

namespace App\Http\Controllers\Admin\Academics;

use App\AramiscClass;
use App\AramiscSection;
use App\tableList;
use App\YearCheck;
use App\AramiscClassSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Scopes\GlobalAcademicScope;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\Academics\ClassRequest;

class GlobalClassController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
    }

    public function index(Request $request)
    {
        try {
            $sections = AramiscSection::withoutGlobalScope(GlobalAcademicScope::class)->where('school_id',auth()->user()->school_id)->whereNULL('parent_id')->get();
            $classes = AramiscClass::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope( StatusAcademicSchoolScope::class)->where('school_id', Auth::user()->school_id)->with('groupclassSections')->whereNULL('parent_id')->get();
            return view('backEnd.global.global_class', compact('classes', 'sections'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function store(ClassRequest $request)
    {
        DB::beginTransaction();
            try {
                $class = new AramiscClass();
                $class->class_name = $request->name;
                $class->parent_id = null;
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
                    DB::commit();
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
           
            } catch (\Exception $e) {
                DB::rollBack();              
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }

    }

    public function edit(Request $request, $id)
    {
        try {
            $classById = SmCLass::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('id', $id)->firstOrFail();
            $sectionByNames = AramiscClassSection::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->select('section_id')->where('class_id', '=', $classById->id)->get();
            $sectionId = array();
            foreach ($sectionByNames as $sectionByName) {
                $sectionId[] = $sectionByName->section_id;
            }
            
            $sections = AramiscSection::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('active_status', '=', 1)->where('school_id', Auth::user()->school_id)->get();
            $classes = AramiscClass::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->with('groupclassSections')->where('school_id', Auth::user()->school_id)->withCount('records')->get();
            return view('backEnd.global.global_class', compact('classById', 'classes', 'sections', 'sectionId'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(ClassRequest $request)
    {
        
        SmCLassSection::withoutGlobalScope(GlobalAcademicScope::class, StatusAcademicSchoolScope::class)->where('class_id', $request->id)->delete();
        DB::beginTransaction();

        try {
            $class = SmCLass::withoutGlobalScope(GlobalAcademicScope::class, StatusAcademicSchoolScope::class)->where('id',$request->id)->firstOrFail();
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
                Toastr::success('Operation successful', 'Success');
                return redirect('global-class');
            } catch (\Exception $e) {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
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
                  $class_sections = AramiscClassSection::withoutGlobalScope(GlobalAcademicScope::class, StatusAcademicSchoolScope::class)->where('class_id', $id)->get();
                    foreach ($class_sections as $key => $class_section) {
                        AramiscClassSection::destroy($class_section->id);
                    }
                   $section = AramiscClass::destroy($id);
                DB::commit();
                
                
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