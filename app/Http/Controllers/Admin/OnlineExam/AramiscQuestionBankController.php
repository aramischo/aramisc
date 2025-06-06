<?php

namespace App\Http\Controllers\Admin\OnlineExam;

use App\AramiscClass;
use App\AramiscStaff;
use App\AramiscSection;
use App\YearCheck;
use App\AramiscQuestionBank;
use App\AramiscAssignSubject;
use App\AramiscQuestionGroup;
use App\AramiscQuestionLevel;
use App\AramiscGeneralSettings;
use Illuminate\Http\Request;
use App\AramiscQuestionBankMuOption;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Schema\Blueprint;
use App\Http\Requests\Admin\OnlineExam\AramiscQuestionBankRequest;

class AramiscQuestionBankController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $levels = AramiscQuestionLevel::get();

            $groups = AramiscQuestionGroup::get();

            $banks = AramiscQuestionBank::withOutGlobalScope(StatusAcademicSchoolScope::class)->with('class', 'section', 'questionMu', 'questionGroup')->get();

            if (teacherAccess()) {
                $teacher_info = AramiscStaff::where('user_id', Auth::user()->id)->first();
                $classes = $teacher_info->classes;
            } else {
                $classes = AramiscClass::get();
            }
            $sections = AramiscSection::get();
            return view('backEnd.examination.question_bank', compact('banks', 'levels', 'groups', 'classes', 'sections'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    function universityQuestionBankStore($request)
    {
        try {
            if ($request->question_type != 'M' && $request->question_type != 'MI') {
                foreach ($request->un_section_ids as $section) {
                    $online_question = new AramiscQuestionBank();
                    $online_question->type = $request->question_type;
                    $online_question->q_group_id = $request->group;
                    $online_question->un_semester_label_id = $request->un_semester_label_id;
                    $online_question->un_section_id = $section;
                    $online_question->un_session_id = $request->un_session_id;
                    $online_question->un_faculty_id = $request->un_faculty_id;
                    $online_question->un_department_id = $request->un_department_id;

                    $online_question->marks = $request->marks;
                    $online_question->question = $request->question;
                    $online_question->school_id = Auth::user()->school_id;
                    $online_question->un_academic_id = getAcademicId();
                    if ($request->question_type == "F") {
                        $online_question->suitable_words = $request->suitable_words;
                    } elseif ($request->question_type == "T") {
                        $online_question->trueFalse = $request->trueOrFalse;
                    }
                    $result = $online_question->save();
                }
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            } elseif ($request->question_type == 'MI') {

                // return $request;

                DB::beginTransaction();

                if (!Schema::hasColumn('aramisc_question_banks', 'question_image')) {
                    Schema::table('aramisc_question_banks', function ($table) {
                        $table->string('question_image')->nullable();
                    });
                }
                if (!Schema::hasColumn('aramisc_question_banks', 'answer_type')) {
                    Schema::table('aramisc_question_banks', function ($table) {
                        $table->string('answer_type')->nullable();
                    });
                }

                try {

                    $fileName = "";
                    $imagemimes = [
                        'image/png',
                        'image/jpg',
                        'image/jpeg'
                    ];

                    $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
                    $file = $request->file('question_image');
                    $fileSize =  filesize($file);
                    $fileSizeKb = ($fileSize / 1000000);
                    if ($fileSizeKb >= $maxFileSize) {
                        Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                        return redirect()->back();
                    }


                    if (($request->file('question_image') != "")  && (in_array($file->getMimeType(), $imagemimes))) {
                        $image_info = getimagesize($request->file('question_image'));
                        if ($image_info[0] <= 650 && $image_info[1] <= 450) {
                            $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                            $file->move('public/uploads/upload_contents/', $fileName);
                            $fileName = 'public/uploads/upload_contents/' . $fileName;
                        } else {
                            Toastr::error('Question Image should be 650x450', 'Failed');
                            // return redirect()->back();
                            return redirect()->to(url()->previous())
                                ->withInput($request->input());
                        }
                    }
                    foreach ($request->section as $section) {
                        $online_question = new AramiscQuestionBank();
                        $online_question->type = $request->question_type;
                        $online_question->q_group_id = $request->group;
                        $online_question->un_semester_label_id = $request->un_semester_label_id;
                        $online_question->un_section_id = $section;
                        $online_question->un_session_id = $request->un_session_id;
                        $online_question->un_faculty_id = $request->un_faculty_id;
                        $online_question->un_department_id = $request->un_department_id;
                        $online_question->marks = $request->marks;
                        $online_question->question = $request->question;
                        $online_question->answer_type = $request->answer_type;
                        $online_question->question_image = $fileName;
                        if ($request->question_type == 'MI') {
                            $online_question->number_of_option = $request->number_of_optionImg;
                        } else {

                            $online_question->number_of_option = $request->number_of_option;
                        }
                        $online_question->school_id = Auth::user()->school_id;
                        $online_question->un_academic_id = getAcademicId();
                        $online_question->save();
                        $online_question->toArray();
                    }
                    $i = 0;
                    if (isset($request->images)) {
                        foreach ($request->images as $key => $image) {
                            $i++;
                            $option_check = 'option_check_' . $i;
                            $online_question_option = new AramiscQuestionBankMuOption();
                            $online_question_option->question_bank_id = $online_question->id;

                            $file = $request->file('images');
                            $fileName = "";
                            if (($file[$key] != "")  && (in_array($file[$key]->getMimeType(), $imagemimes))) {
                                $fileName = md5($file[$key]->getClientOriginalName() . time()) . "." . $file[$key]->getClientOriginalExtension();
                                $file[$key]->move('public/uploads/upload_contents/', $fileName);
                                $fileName = 'public/uploads/upload_contents/' . $fileName;
                            }

                            $online_question_option->title = $fileName;

                            $online_question_option->school_id = Auth::user()->school_id;
                            $online_question_option->un_academic_id = getAcademicId();
                            if (isset($request->$option_check)) {
                                $online_question_option->status = 1;
                            } else {
                                $online_question_option->status = 0;
                            }
                            $online_question_option->save();
                        }
                    }
                    DB::commit();
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } catch (\Exception $e) {
                    DB::rollBack();
                }
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            } else {
                DB::beginTransaction();

                try {
                    foreach ($request->section as $section) {

                        $online_question = new AramiscQuestionBank();
                        $online_question->type = $request->question_type;
                        $online_question->q_group_id = $request->group;
                        $online_question->un_semester_label_id = $request->un_semester_label_id;
                        $online_question->un_section_id = $section;
                        $online_question->un_session_id = $request->un_session_id;
                        $online_question->un_faculty_id = $request->un_faculty_id;
                        $online_question->un_department_id = $request->un_department_id;
                        $online_question->marks = $request->marks;
                        $online_question->question = $request->question;
                        $online_question->number_of_option = $request->number_of_option;
                        $online_question->school_id = Auth::user()->school_id;
                        $online_question->un_academic_id = getAcademicId();
                        $online_question->save();
                        $online_question->toArray();
                        $i = 0;
                        if (isset($request->option)) {
                            $sel = 0;
                            foreach ($request->option as $option) {
                                $i++;
                                $option_check = 'option_check_' . $i;
                                if ($request->$option_check) {
                                    $sel = $request->$option_check;
                                    break;
                                }
                            }

                            if ($sel == 0) {
                                Toastr::warning('Please choose correct option', 'warning');
                                return redirect()->back();
                            }
                            foreach ($request->option as $option) {
                                $i++;
                                $option_check = 'option_check_' . $i;
                                $online_question_option = new AramiscQuestionBankMuOption();
                                $online_question_option->question_bank_id = $online_question->id;
                                $online_question_option->title = $option;
                                $online_question_option->school_id = Auth::user()->school_id;
                                $online_question_option->un_academic_id = getAcademicId();
                                if (isset($request->$option_check)) {
                                    $online_question_option->status = 1;
                                } else {
                                    $online_question_option->status = 0;
                                }
                                $online_question_option->save();
                            }
                        }
                    }
                    DB::commit();
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } catch (\Exception $e) {
                    DB::rollBack();
                }
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function store(AramiscQuestionBankRequest $request)
    {

        if (moduleStatusCheck('University')) {
            return   $this->universityQuestionBankStore($request);
        } else {

            try {

                if ($request->question_type != 'M' && $request->question_type != 'MI') {
                    foreach ($request->section as $section) {
                        $online_question = new AramiscQuestionBank();
                        $online_question->type = $request->question_type;
                        $online_question->q_group_id = $request->group;
                        $online_question->class_id = $request->class;
                        $online_question->section_id = $section;
                        $online_question->marks = $request->marks;
                        $online_question->question = $request->question;
                        $online_question->school_id = Auth::user()->school_id;
                        $online_question->academic_id = getAcademicId();
                        if ($request->question_type == "F") {
                            $online_question->suitable_words = $request->suitable_words;
                        } elseif ($request->question_type == "T") {
                            $online_question->trueFalse = $request->trueOrFalse;
                        }
                        $result = $online_question->save();
                    }
                    if ($result) {
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                } elseif ($request->question_type == 'MI') {

                    // return $request;

                    DB::beginTransaction();

                    if (!Schema::hasColumn('aramisc_question_banks', 'question_image')) {
                        Schema::table('aramisc_question_banks', function ($table) {
                            $table->string('question_image')->nullable();
                        });
                    }
                    if (!Schema::hasColumn('aramisc_question_banks', 'answer_type')) {
                        Schema::table('aramisc_question_banks', function ($table) {
                            $table->string('answer_type')->nullable();
                        });
                    }

                    try {

                        $fileName = "";
                        $imagemimes = [
                            'image/png',
                            'image/jpg',
                            'image/jpeg'
                        ];

                        $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
                        $file = $request->file('question_image');
                        $fileSize =  filesize($file);
                        $fileSizeKb = ($fileSize / 1000000);
                        if ($fileSizeKb >= $maxFileSize) {
                            Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                            return redirect()->back();
                        }


                        if (($request->file('question_image') != "")  && (in_array($file->getMimeType(), $imagemimes))) {
                            $image_info = getimagesize($request->file('question_image'));
                            if ($image_info[0] <= 650 && $image_info[1] <= 450) {
                                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                                $file->move('public/uploads/upload_contents/', $fileName);
                                $fileName = 'public/uploads/upload_contents/' . $fileName;
                            } else {
                                Toastr::error('Question Image should be 650x450', 'Failed');
                                // return redirect()->back();
                                return redirect()->to(url()->previous())
                                    ->withInput($request->input());
                            }
                        }
                        foreach ($request->section as $section) {
                            $online_question = new AramiscQuestionBank();
                            $online_question->type = $request->question_type;
                            $online_question->q_group_id = $request->group;
                            $online_question->class_id = $request->class;
                            $online_question->section_id = $section;
                            $online_question->marks = $request->marks;
                            $online_question->question = $request->question;
                            $online_question->answer_type = $request->answer_type;
                            $online_question->question_image = $fileName;
                            if ($request->question_type == 'MI') {
                                $online_question->number_of_option = $request->number_of_optionImg;
                            } else {

                                $online_question->number_of_option = $request->number_of_option;
                            }
                            $online_question->school_id = Auth::user()->school_id;
                            $online_question->academic_id = getAcademicId();
                            $online_question->save();
                            $online_question->toArray();
                        }
                        $i = 0;
                        if (isset($request->images)) {
                            foreach ($request->images as $key => $image) {
                                $i++;
                                $option_check = 'option_check_' . $i;
                                $online_question_option = new AramiscQuestionBankMuOption();
                                $online_question_option->question_bank_id = $online_question->id;

                                $file = $request->file('images');
                                $fileName = "";
                                if (($file[$key] != "")  && (in_array($file[$key]->getMimeType(), $imagemimes))) {
                                    $fileName = md5($file[$key]->getClientOriginalName() . time()) . "." . $file[$key]->getClientOriginalExtension();
                                    $file[$key]->move('public/uploads/upload_contents/', $fileName);
                                    $fileName = 'public/uploads/upload_contents/' . $fileName;
                                }

                                $online_question_option->title = $fileName;

                                $online_question_option->school_id = Auth::user()->school_id;
                                $online_question_option->academic_id = getAcademicId();
                                if (isset($request->$option_check)) {
                                    $online_question_option->status = 1;
                                } else {
                                    $online_question_option->status = 0;
                                }
                                $online_question_option->save();
                            }
                        }
                        DB::commit();
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } catch (\Exception $e) {
                        DB::rollBack();
                    }
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                } else {
                    DB::beginTransaction();

                    try {
                        foreach ($request->section as $section) {

                            $online_question = new AramiscQuestionBank();
                            $online_question->type = $request->question_type;
                            $online_question->q_group_id = $request->group;
                            $online_question->class_id = $request->class;
                            $online_question->section_id = $section;
                            $online_question->marks = $request->marks;
                            $online_question->question = $request->question;
                            $online_question->number_of_option = $request->number_of_option;
                            $online_question->school_id = Auth::user()->school_id;
                            $online_question->academic_id = getAcademicId();
                            $online_question->save();
                            $online_question->toArray();
                            $i = 0;
                            if (isset($request->option)) {
                                $sel = 0;
                                foreach ($request->option as $option) {
                                    $i++;
                                    $option_check = 'option_check_' . $i;
                                    if ($request->$option_check) {
                                        $sel = $request->$option_check;
                                        break;
                                    }
                                }

                                if ($sel == 0) {
                                    Toastr::warning('Please choose correct option', 'warning');
                                    return redirect()->back();
                                }
                                foreach ($request->option as $option) {
                                    $i++;
                                    $option_check = 'option_check_' . $i;
                                    $online_question_option = new AramiscQuestionBankMuOption();
                                    $online_question_option->question_bank_id = $online_question->id;
                                    $online_question_option->title = $option;
                                    $online_question_option->school_id = Auth::user()->school_id;
                                    $online_question_option->academic_id = getAcademicId();
                                    if (isset($request->$option_check)) {
                                        $online_question_option->status = 1;
                                    } else {
                                        $online_question_option->status = 0;
                                    }
                                    $online_question_option->save();
                                }
                            }
                        }
                        DB::commit();
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } catch (\Exception $e) {
                        DB::rollBack();
                    }
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            } catch (\Exception $e) {

                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    }


    public function show($id)
    {
        try {
            $levels = AramiscQuestionLevel::get();
            $groups = AramiscQuestionGroup::get();
            $banks  = AramiscQuestionBank::get();
            $bank   = AramiscQuestionBank::find($id);
            if (teacherAccess()) {
                $teacher_info = AramiscStaff::where('user_id', Auth::user()->id)->first();
                $classes  = $teacher_info->classes;
            } else {
                $classes = AramiscClass::get();
            }
            $sections = AramiscSection::get();
            $editData = $bank; // For university module
            return view('backEnd.examination.question_bank', compact('levels', 'groups', 'banks', 'bank', 'classes', 'sections', 'editData'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function edit($id)
    {
        //
    }

    function universityBankUpdate($request, $id)
    {
        try {
            if ($request->question_type != 'M' && $request->question_type != 'MI') {
                $online_question = AramiscQuestionBank::find($id);
                $online_question->type = $request->question_type;
                $online_question->q_group_id = $request->group;
                $online_question->un_semester_label_id = $request->un_semester_label_id;
                $online_question->un_section_id = $request->un_section_id;
                $online_question->un_session_id = $request->un_session_id;
                $online_question->un_faculty_id = $request->un_faculty_id;
                $online_question->un_department_id = $request->un_department_id;
                $online_question->marks = $request->marks;
                $online_question->question = $request->question;
                if ($request->question_type == "F") {
                    $online_question->suitable_words = $request->suitable_words;
                } elseif ($request->question_type == "T") {
                    $online_question->trueFalse = $request->trueOrFalse;
                }
                $result = $online_question->save();
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('question-bank');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            } elseif ($request->question_type == 'MI') {
                DB::beginTransaction();

                if (!Schema::hasColumn('aramisc_question_banks', 'question_image')) {
                    Schema::table('aramisc_question_banks', function ($table) {
                        $table->string('question_image')->nullable();
                    });
                }

                try {


                    $online_question = AramiscQuestionBank::find($id);
                    $online_question->type = $request->question_type;
                    $online_question->q_group_id = $request->group;
                    $online_question->un_semester_label_id = $request->un_semester_label_id;
                    $online_question->un_section_id = $request->un_section_id;
                    $online_question->un_session_id = $request->un_session_id;
                    $online_question->un_faculty_id = $request->un_faculty_id;
                    $online_question->un_department_id = $request->un_department_id;
                    $online_question->marks = $request->marks;
                    $online_question->question = $request->question;
                    $online_question->answer_type = $request->answer_type;
                    if ($request->question_type == 'MI') {
                        $online_question->number_of_option = $request->number_of_optionImg;
                    } else {
                        $online_question->number_of_option = $request->number_of_option;
                    }
                    $fileName = $online_question->question_image;
                    $imagemimes = [
                        'image/png',
                        'image/jpg',
                        'image/jpeg'
                    ];

                    $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
                    $file = $request->file('question_image');
                    $fileSize =  filesize($file);
                    $fileSizeKb = ($fileSize / 1000000);
                    if ($fileSizeKb >= $maxFileSize) {
                        Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                        return redirect()->back();
                    }




                    if (($request->file('question_image') != "")  && (in_array($file->getMimeType(), $imagemimes))) {
                        $image_info = getimagesize($request->file('question_image'));
                        if ($image_info[0] <= 650 && $image_info[1] <= 450) {
                            $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                            $file->move('public/uploads/upload_contents/', $fileName);
                            $fileName = 'public/uploads/upload_contents/' . $fileName;
                        } else {
                            Toastr::error('Question Image should be 650x450', 'Failed');
                            return redirect()->to(url()->previous())
                                ->withInput($request->input());
                        }
                    }

                    $online_question->question_image = $fileName;

                    $online_question->number_of_option = $request->number_of_option;
                    $online_question->school_id = Auth::user()->school_id;
                    $online_question->un_academic_id = getAcademicId();
                    $online_question->save();
                    $online_question->toArray();
                    $i = 0;

                    if (isset($request->images_old)) {
                        AramiscQuestionBankMuOption::where('question_bank_id', $online_question->id)->delete();
                        foreach ($request->images_old as $key => $image) {
                            $i++;
                            $option_check = 'option_check_' . $i;
                            $online_question_option = new AramiscQuestionBankMuOption();
                            $online_question_option->question_bank_id = $online_question->id;

                            if (isset($request->images[$key])) {

                                $file = $request->file('images');
                                $fileName = "";
                                if (($file[$key] != "")  && (in_array($file[$key]->getMimeType(), $imagemimes))) {
                                    $fileName = md5($file[$key]->getClientOriginalName() . time()) . "." . $file[$key]->getClientOriginalExtension();
                                    $file[$key]->move('public/uploads/upload_contents/', $fileName);
                                    $fileName = 'public/uploads/upload_contents/' . $fileName;
                                }
                            } else {
                                $fileName = $request->images_old[$key];
                            }



                            $online_question_option->title = $fileName;

                            $online_question_option->school_id = Auth::user()->school_id;
                            $online_question_option->academic_id = getAcademicId();
                            if (isset($request->$option_check)) {
                                $online_question_option->status = 1;
                            } else {
                                $online_question_option->status = 0;
                            }
                            $online_question_option->save();
                        }
                    }
                    DB::commit();
                    Toastr::success('Operation successful', 'Success');
                    return redirect('question-bank');
                } catch (\Exception $e) {
                    DB::rollBack();
                }
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            } else {
                DB::beginTransaction();
                try {
                    $online_question = AramiscQuestionBank::find($id);
                    $online_question->type = $request->question_type;
                    $online_question->q_group_id = $request->group;
                    $online_question->un_semester_label_id = $request->un_semester_label_id;
                    $online_question->un_section_id = $request->un_section_id;
                    $online_question->un_session_id = $request->un_session_id;
                    $online_question->un_faculty_id = $request->un_faculty_id;
                    $online_question->un_department_id = $request->un_department_id;
                    $online_question->marks = $request->marks;
                    $online_question->question = $request->question;
                    $online_question->number_of_option = $request->number_of_option;
                    $online_question->save();
                    $online_question->toArray();
                    $i = 0;
                    if (isset($request->option)) {
                        AramiscQuestionBankMuOption::where('question_bank_id', $online_question->id)->delete();
                        foreach ($request->option as $option) {
                            $i++;
                            $option_check = 'option_check_' . $i;
                            $online_question_option = new AramiscQuestionBankMuOption();
                            $online_question_option->question_bank_id = $online_question->id;
                            $online_question_option->title = $option;
                            $online_question_option->school_id = Auth::user()->school_id;
                            $online_question_option->un_academic_id = getAcademicId();
                            if (isset($request->$option_check)) {
                                $online_question_option->status = 1;
                            } else {
                                $online_question_option->status = 0;
                            }
                            $online_question_option->save();
                        }
                    }
                    DB::commit();
                    Toastr::success('Operation successful', 'Success');
                    return redirect('question-bank');
                } catch (\Exception $e) {
                    DB::rollBack();
                }
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(AramiscQuestionBankRequest $request, $id)
    {

        if (moduleStatusCheck('University')) {
            return $this->universityBankUpdate($request, $id);
        } else {
            try {
                if ($request->question_type != 'M' && $request->question_type != 'MI') {
                    $online_question = AramiscQuestionBank::find($id);
                    $online_question->type = $request->question_type;
                    $online_question->q_group_id = $request->group;
                    $online_question->class_id = $request->class;
                    $online_question->section_id = $request->section;
                    $online_question->marks = $request->marks;
                    $online_question->question = $request->question;
                    if ($request->question_type == "F") {
                        $online_question->suitable_words = $request->suitable_words;
                    } elseif ($request->question_type == "T") {
                        $online_question->trueFalse = $request->trueOrFalse;
                    }
                    $result = $online_question->save();
                    if ($result) {
                        Toastr::success('Operation successful', 'Success');
                        return redirect('question-bank');
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                } elseif ($request->question_type == 'MI') {
                    DB::beginTransaction();

                    if (!Schema::hasColumn('aramisc_question_banks', 'question_image')) {
                        Schema::table('aramisc_question_banks', function ($table) {
                            $table->string('question_image')->nullable();
                        });
                    }

                    try {


                        $online_question = AramiscQuestionBank::find($id);
                        $online_question->type = $request->question_type;
                        $online_question->q_group_id = $request->group;
                        $online_question->class_id = $request->class;
                        $online_question->section_id = $request->section;
                        $online_question->marks = $request->marks;
                        $online_question->question = $request->question;
                        $online_question->answer_type = $request->answer_type;
                        if ($request->question_type == 'MI') {
                            $online_question->number_of_option = $request->number_of_optionImg;
                        } else {
                            $online_question->number_of_option = $request->number_of_option;
                        }
                        $fileName = $online_question->question_image;
                        $imagemimes = [
                            'image/png',
                            'image/jpg',
                            'image/jpeg'
                        ];

                        $maxFileSize = AramiscGeneralSettings::first('file_size')->file_size;
                        $file = $request->file('question_image');
                        $fileSize =  filesize($file);
                        $fileSizeKb = ($fileSize / 1000000);
                        if ($fileSizeKb >= $maxFileSize) {
                            Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                            return redirect()->back();
                        }




                        if (($request->file('question_image') != "")  && (in_array($file->getMimeType(), $imagemimes))) {
                            $image_info = getimagesize($request->file('question_image'));
                            if ($image_info[0] <= 650 && $image_info[1] <= 450) {
                                $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                                $file->move('public/uploads/upload_contents/', $fileName);
                                $fileName = 'public/uploads/upload_contents/' . $fileName;
                            } else {
                                Toastr::error('Question Image should be 650x450', 'Failed');
                                return redirect()->to(url()->previous())
                                    ->withInput($request->input());
                            }
                        }

                        $online_question->question_image = $fileName;

                        $online_question->number_of_option = $request->number_of_option;
                        $online_question->school_id = Auth::user()->school_id;
                        $online_question->academic_id = getAcademicId();
                        $online_question->save();
                        $online_question->toArray();
                        $i = 0;

                        if (isset($request->images_old)) {
                            AramiscQuestionBankMuOption::where('question_bank_id', $online_question->id)->delete();
                            foreach ($request->images_old as $key => $image) {
                                $i++;
                                $option_check = 'option_check_' . $i;
                                $online_question_option = new AramiscQuestionBankMuOption();
                                $online_question_option->question_bank_id = $online_question->id;

                                if (isset($request->images[$key])) {

                                    $file = $request->file('images');
                                    $fileName = "";
                                    if (($file[$key] != "")  && (in_array($file[$key]->getMimeType(), $imagemimes))) {
                                        $fileName = md5($file[$key]->getClientOriginalName() . time()) . "." . $file[$key]->getClientOriginalExtension();
                                        $file[$key]->move('public/uploads/upload_contents/', $fileName);
                                        $fileName = 'public/uploads/upload_contents/' . $fileName;
                                    }
                                } else {
                                    $fileName = $request->images_old[$key];
                                }



                                $online_question_option->title = $fileName;

                                $online_question_option->school_id = Auth::user()->school_id;
                                $online_question_option->academic_id = getAcademicId();
                                if (isset($request->$option_check)) {
                                    $online_question_option->status = 1;
                                } else {
                                    $online_question_option->status = 0;
                                }
                                $online_question_option->save();
                            }
                        }
                        DB::commit();
                        Toastr::success('Operation successful', 'Success');
                        return redirect('question-bank');
                    } catch (\Exception $e) {
                        DB::rollBack();
                    }
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                } else {
                    DB::beginTransaction();
                    try {
                        $online_question = AramiscQuestionBank::find($id);
                        $online_question->type = $request->question_type;
                        $online_question->q_group_id = $request->group;
                        $online_question->class_id = $request->class;
                        $online_question->section_id = $request->section;
                        $online_question->marks = $request->marks;
                        $online_question->question = $request->question;
                        $online_question->number_of_option = $request->number_of_option;
                        $online_question->save();
                        $online_question->toArray();
                        $i = 0;
                        if (isset($request->option)) {
                            AramiscQuestionBankMuOption::where('question_bank_id', $online_question->id)->delete();
                            foreach ($request->option as $option) {
                                $i++;
                                $option_check = 'option_check_' . $i;
                                $online_question_option = new AramiscQuestionBankMuOption();
                                $online_question_option->question_bank_id = $online_question->id;
                                $online_question_option->title = $option;
                                $online_question_option->school_id = Auth::user()->school_id;
                                $online_question_option->academic_id = getAcademicId();
                                if (isset($request->$option_check)) {
                                    $online_question_option->status = 1;
                                } else {
                                    $online_question_option->status = 0;
                                }
                                $online_question_option->save();
                            }
                        }
                        DB::commit();
                        Toastr::success('Operation successful', 'Success');
                        return redirect('question-bank');
                    } catch (\Exception $e) {
                        DB::rollBack();
                    }
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    }


    public function destroy($id)
    {
        $tables = \App\tableList::getTableList('question_bank_id', $id);

        
        $online_question = AramiscQuestionBank::find($id);
        if ($online_question->type != "M") {
            $tables = \App\tableList::getTableList('question_bank_id', $id);
        } else {
            $tables = null;
        }
        try {
            if ($tables == null) {
                if ($online_question->type == "M") {
                    AramiscQuestionBankMuOption::where('question_bank_id', $online_question->id)->delete();
                }

                $online_question->delete();

                Toastr::success('Operation successful', 'Success');
                return redirect('question-bank');
            } else {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error($msg, 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
            Toastr::error($msg, 'Failed');
            return redirect()->back();
        }
    }
}
