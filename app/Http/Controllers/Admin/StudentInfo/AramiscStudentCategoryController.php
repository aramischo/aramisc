<?php

namespace App\Http\Controllers\Admin\StudentInfo;

use App\AramiscStudentCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\StudentInfo\AramiscStudentCategoryRequest;

class AramiscStudentCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
    }

    public function index(Request $request)
    {
        try {
            $student_types = AramiscStudentCategory::get();
            return view('backEnd.studentInformation.student_category', compact('student_types'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function store(AramiscStudentCategoryRequest $request)
    {
        try {
            $student_type = new AramiscStudentCategory();
            $student_type->category_name = $request->category;
            $student_type->school_id = Auth::user()->school_id;
            $student_type->academic_id = getAcademicId();
            $student_type->save();
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $student_type = AramiscStudentCategory::find($id);
            $student_types = AramiscStudentCategory::get();
            return view('backEnd.studentInformation.student_category', compact('student_types', 'student_type'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function update(AramiscStudentCategoryRequest $request)
    {
        try {
            $student_type = AramiscStudentCategory::find($request->id);
            $student_type->category_name = $request->category;
            $student_type->save();

            Toastr::success('Operation successful', 'Success');
            return redirect('student-category');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $tables = \App\tableList::getTableList('student_category_id', $id);
            try {
                if ($tables==null) {
                    AramiscStudentCategory::find($id)->delete();
                } else {
                    $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error($msg, 'Failed');
                return redirect()->back();
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
