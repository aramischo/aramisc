<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\AramiscStaff;
use Illuminate\Http\Request;
use App\Models\AramiscExpertTeacher;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Modules\RolePermission\Entities\AramiscRole;

class AramiscExpertTeacherController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
    }
    public function index()
    {
        try {
            $expertTeachers = AramiscExpertTeacher::where('school_id', auth()->user()->school_id)->orderBy('position', 'asc')->with('staff.designations')->get();
            $roles = AramiscRole::where('active_status', '=', '1')
                ->whereNotIn('id', [1, 2, 3, 10])
                ->where(function ($q) {
                    $q->where('school_id', auth()->user()->school_id)->orWhere('type', 'System');
                })->get();
                // dd($expertTeachers);
            return view('backEnd.frontSettings.expert_teacher.expert_teacher', compact('expertTeachers', 'roles'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function store(Request $request)
    {
        try {
            $staffExists = AramiscExpertTeacher::where('staff_id', $request->staff)->first();
            if ($staffExists == null) {
                $expertTeacher = new AramiscExpertTeacher();
                $expertTeacher->staff_id = $request->staff;
                $expertTeacher->created_by = auth()->user()->id;
                $expertTeacher->school_id = auth()->user()->school_id;
                $expertTeacher->save();

                $staff = AramiscStaff::find($request->staff);
                if ($staff != null) {
                    $staff->show_public = 1;
                    $staff->update();
                }

                Toastr::success('Operation successful', 'Success');
                return redirect()->route('expert-teacher');
            } else {
                Toastr::error('Already Set As Expert Staff', 'Failed');
                return redirect()->route('expert-teacher');
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function deleteModal($id)
    {
        try {
            $expertTeacher = AramiscExpertTeacher::find($id);
            return view('backEnd.frontSettings.expert_teacher.expert_teacher_delete_modal', compact('expertTeacher'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function delete($id)
    {
        try {
            $expertTeacher = AramiscExpertTeacher::where('id', $id)->first();

            $staff = AramiscStaff::find($expertTeacher->staff_id);
            if ($staff != null) {
                $staff->show_public = 0;
                $staff->update();
            }

            $expertTeacher->delete();
            Toastr::success('Deleted successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
