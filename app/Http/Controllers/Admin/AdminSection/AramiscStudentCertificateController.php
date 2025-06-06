<?php

namespace App\Http\Controllers\Admin\AdminSection;

use App\AramiscClass;
use App\AramiscStudent;
use App\Models\StudentRecord;
use App\AramiscStudentCertificate;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\Alumni\Entities\Graduate;
use App\Http\Requests\Admin\AdminSection\AramiscStudentCertificateRequest;
use App\Http\Requests\Admin\AdminSection\GenerateCertificateSearchRequest;


class AramiscStudentCertificateController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }


    public function index()
    {

        try {
            $certificates = AramiscStudentCertificate::where('active_status', 1)->where('school_id',Auth::user()->school_id)->get();
            return view('backEnd.admin.student_certificate', compact('certificates'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function store(AramiscStudentCertificateRequest $request)
    {
        #try {
            $destination = 'public/uploads/certificate/';
            $fileName = fileUpload($request->file,$destination);
            $certificate = new AramiscStudentCertificate();
            $certificate->name = $request->name;
            $certificate->header_left_text = $request->header_left_text;
            $certificate->date = date('Y-m-d', strtotime($request->date));
            $certificate->body = $request->body;
            $certificate->footer_left_text = $request->footer_left_text;
            $certificate->footer_center_text = $request->footer_center_text;
            $certificate->footer_right_text = $request->footer_right_text;
            $certificate->student_photo = $request->student_photo;
            $certificate->file = $fileName;
            $certificate->body_font_family = $request->body_font_family;
            $certificate->layout = $request->layout;
            $certificate->body_font_size = $request->body_font_size;
            $certificate->height = $request->height;
            $certificate->width = $request->width;
            $certificate->school_id = Auth::user()->school_id;
            $certificate->academic_id = getAcademicId();

            $result = $certificate->save();

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();

        // } catch (\Exception $e) {
        //     Toastr::error('Operation Failed', 'Failed');
        //     return redirect()->back();
        // }
    }

    public function edit($id)
    {

        try {
            if (checkAdmin()) {
                $certificate = AramiscStudentCertificate::find($id);
            }else{
                $certificate = AramiscStudentCertificate::where('id',$id)->first();
            }
            $certificates = AramiscStudentCertificate::where('school_id',Auth::user()->school_id)->get();
            return view('backEnd.admin.student_certificate', compact('certificates', 'certificate'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(AramiscStudentCertificateRequest $request, $id)
    {
        try {
            $destination = 'public/uploads/certificate/';
            if (checkAdmin()) {
                $certificate = AramiscStudentCertificate::find($request->id);
            }else{
                $certificate = AramiscStudentCertificate::where('id',$request->id)->where('school_id',Auth::user()->school_id)->first();
            }
            $certificate->name = $request->name;
            $certificate->header_left_text = $request->header_left_text;
            $certificate->date = date('Y-m-d', strtotime($request->date));
            $certificate->body = $request->body;
            if($request->body_two){
                $certificate->body_two = $request->body_two;
            }
            $certificate->footer_left_text = $request->footer_left_text;
            $certificate->footer_center_text = $request->footer_center_text;
            $certificate->footer_right_text = $request->footer_right_text;
            $certificate->student_photo = $request->student_photo;
            $certificate->certificate_no = $request->certificate_no;
            $certificate->body_font_family = $request->body_font_family;
            $certificate->layout = $request->layout;
            $certificate->body_font_size = $request->body_font_size;
            $certificate->height = $request->height;
            $certificate->width = $request->width;
            $certificate->file = fileUpdate($certificate->file,$request->file,$destination);
            //   uest->all());
            $result = $certificate->save();

            Toastr::success('Operation successful', 'Success');
            return redirect('student-certificate');

        } catch (\Exception $e) {
             ;
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function destroy($id)
    {

        try {
            // $certificate = AramiscStudentCertificate::find($id);
            if (checkAdmin()) {
                $certificate = AramiscStudentCertificate::find($id);
            }else{
                $certificate = AramiscStudentCertificate::where('id',$id)->where('school_id',Auth::user()->school_id)->first();
            }
            if($certificate->file){
                unlink($certificate->file);
            }
            $result = $certificate->delete();

            Toastr::success('Operation successful', 'Success');
            return redirect('student-certificate');

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    // for get route
    public function generateCertificate()
    {

        try {
            $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id',Auth::user()->school_id)->get();
            $certificates = AramiscStudentCertificate::where('school_id',auth()->user()->school_id)->get();
            return view('backEnd.admin.generate_certificate', compact('classes', 'certificates'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    
    public function setDefault($certificate_id,$default_for){
        try {
            $certificate = AramiscStudentCertificate::find($certificate_id);
            $certificate->default_for = $default_for;
            $result = $certificate->save();
            if($result){
                Toastr::success('Certificate Set As Default for '.$default_for, 'Success');
                return redirect()->back();
            }else{
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function resetDefault($certificate_id){
        try {
            $certificate = AramiscStudentCertificate::find($certificate_id);
            $certificate->default_for = null;
            $result = $certificate->save();
            if($result){
                Toastr::success('Certificate Reset For Default', 'Success');
                return redirect()->back();
            }else{
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    // for post route
    public function generateCertificateSearch(GenerateCertificateSearchRequest $request)
    {

        try {
            $certificate_id = $request->certificate;
            $certificates = AramiscStudentCertificate::where('active_status', 1)->where('school_id',Auth::user()->school_id)->get();
            if(moduleStatusCheck('University')){
                $graduates = Graduate::query();
                $graduates->when($request->un_session_id, function ($query) use ($request) {
                    $query->where('un_session_id', $request->un_session_id);
                });
                $graduates->when($request->un_faculty_id, function ($query) use ($request) {
                    $query->where('un_faculty_id', $request->un_faculty_id);
                });
                $graduates->when($request->un_department_id, function ($query) use ($request) {
                    $query->where('un_department_id', $request->un_department_id);
                });
                 $graduates->where('school_id',auth()->user()->school_id)->with('student','unSession','unFaculty','unDepartment');
                 $graduates = $graduates->get();
                 return view('university::un_generate_certificate', compact('graduates', 'certificates', 'certificate_id'));

            }else{
                $class_id = $request->class;
                $classes = AramiscClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id',Auth::user()->school_id)->get();
                
                $students = StudentRecord::when($request->academic_year, function ($query) use ($request) {
                        $query->where('academic_id', $request->academic_year);
                    })
                    ->when($request->class, function ($query) use ($request) {
                        $query->where('class_id', $request->class);
                    })
                    ->when($request->section, function ($query) use ($request) {
                        $query->where('section_id', $request->section);
                    })
                    ->when(!$request->academic_year, function ($query) use ($request) {
                        $query->where('academic_id', getAcademicId());
                    })->where('school_id', auth()->user()->school_id)->get();

                    return view('backEnd.admin.generate_certificate', compact('classes', 'certificates', 'certificate_id', 'certificates', 'students', 'class_id'));
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function generateCertificateGenerate($s_id, $c_id)
    {
        try {
            $s_ids = explode('-', $s_id);
            $students = [];
            foreach ($s_ids as $sId) {
                $students[] = StudentRecord::with('student')->find($sId);
            }

            $certificate = AramiscStudentCertificate::find($c_id);
            if(moduleStatusCheck('University')){
                if($certificate->type ="arabic"){
                    return view('backEnd.admin.generate_arabic_certificate', compact('students', 'certificate'));
                 }
            }else{
                return view('backEnd.admin.student_certificate_print', ['students' => $students, 'certificate' => $certificate]);
                $pdf = Pdf::loadView('backEnd.admin.student_certificate_print', ['students' => $students, 'certificate' => $certificate]);
                $pdf->setPaper('A4', 'landscape');
                return $pdf->stream('certificate.pdf');
            }


        } catch (\Exception $e) {
         
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


}