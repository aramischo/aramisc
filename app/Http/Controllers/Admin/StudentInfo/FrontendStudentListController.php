<?php

namespace App\Http\Controllers\Admin\StudentInfo;

use App\AramiscClass;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscAcademicYear;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FrontendStudentListController extends Controller
{
    public function ajaxFrontendClass(Request $request)
    {
        $classes = AramiscClass::where('academic_id', $request->year)
            ->where('school_id', app('school')->id)
            ->get();
        return response()->json([$classes]);
    }
    public function ajaxFrontendSection(Request $request)
    {
        $sections = AramiscSection::where('academic_id', $request->class)
            ->where('school_id', app('school')->id)
            ->get();
        return response()->json([$sections]);
    }
    public function getStudents(Request $request)
    {
        $data['academicYears'] = AramiscAcademicYear::get();
        $data['students'] = AramiscStudent::when($request->academic_year, function ($q) use ($request) {
            $q->where('academic_id', $request->academic_year);
        })
            ->when($request->class, function ($q) use ($request) {
                $q->whereHas('studentRecord', function ($query) use ($request) {
                    $query->where('class_id', $request->class);
                });
            })
            ->when($request->section, function ($q) use ($request) {
                $q->whereHas('studentRecord', function ($query) use ($request) {
                    $query->where('section_id', $request->section);
                });
            })->get();
        return view('components.' . activeTheme() . '.frontend-student-list', $data);
    }
}
