<?php

namespace App\Http\Controllers\Admin\FeesCollection;

use App\User;
use App\AramiscClass;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscFeesAssign;
use App\ApiBaseMethod;
use App\AramiscFeesPayment;
use App\AramiscGeneralSettings;
use App\Models\FeesInvoice;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\AramiscFeesAssignDiscount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\DirectFeesInstallmentAssign;
use Modules\University\Entities\UnFeesInstallmentAssign;
use App\Http\Requests\Admin\FeesCollection\AramiscFeesCollectSearchRequest;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class AramiscFeesCollectController extends Controller
{

    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }
    public function index(Request $request)
    {
        try {
            $classes = AramiscClass::get();
            return view('backEnd.feesCollection.collect_fees', compact('classes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function search(AramiscFeesCollectSearchRequest $request)
    {
        try {
            $data = [];
            $students = StudentRecord::query();
            if (moduleStatusCheck('University')) {
                $students =  universityFilter($students, $request);
                $students = $students->with('studentDetail.parents')
                    ->whereHas('studentDetail', function ($q) {
                        $q->where('active_status', 1);
                    })->get();
            } else {
                if ($request->class != "") {
                    $students->where('class_id', $request->class);
                } else {
                    $students->whereNotNull('class_id');
                }
                if ($request->section != "") {
                    $students->where('section_id', $request->section);
                } else {
                    $students->whereNotNull('section_id');
                }
                if ($request->keyword != "") {
                    $students->whereHas('studentDetail', function ($q) use ($request) {
                        $q->where('full_name', 'like', '%' . $request->keyword . '%')->orWhere('admission_no', $request->keyword)->orWhere('roll_no', $request->keyword)->orWhere('national_id_no', $request->keyword)->orWhere('local_id_no', $request->keyword);
                    });
                }
                $students = $students->with('class', 'section', 'studentDetail.parents')
                    ->whereHas('studentDetail', function ($q) {
                        $q->where('active_status', 1);
                    })->get();
            }


            if ($students->isEmpty()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('No result found');
                }
                Toastr::error('No result found', 'Failed');
                return redirect('collect-fees');
            }
            $classes = AramiscClass::get();

            $class_info = AramiscClass::find($request->class);
            $search_info['class_name'] = @$class_info->class_name;
            if ($request->section != "") {
                $section_info = AramiscSection::find($request->section);
                $search_info['section_name'] = @$section_info->section_name;
            }
            if ($request->keyword != "") {
                $search_info['keyword'] = $request->keyword;
            }
            $data['classes'] = $classes;
            $data['students'] = $students;
            $data['search_info'] = $search_info;

            if (moduleStatusCheck('University')) {
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data += $interface->getCommonData($request);
            }

            return view('backEnd.feesCollection.collect_fees', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function collectFeesStudent(Request $request, $id)
    {
        try {

            $student = StudentRecord::with('studentDetail', 'feesDiscounts', 'fees')->find($id);
            $fees_assigneds = AramiscFeesAssign::with('feesGroupMaster')
                ->where('student_id', $student->student_id)
                ->where('record_id', $id)
                ->orderBy('id', 'desc')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            if (count($fees_assigneds) <= 0) {
                Toastr::warning('Fees not assigned yet!');
                return redirect('/collect-fees');
            }

            $fees_discounts = $student->feesDiscounts;

            $applied_discount = [];
            foreach ($fees_discounts as $fees_discount) {
                $fees_payment = AramiscFeesPayment::select('fees_discount_id')
                    ->where('active_status', 1)
                    ->where('record_id', $id)
                    ->where('fees_discount_id', $fees_discount->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();

                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }

            $data['student'] = $student;
            $data['invoice_settings'] = FeesInvoice::where('school_id', auth()->user()->school_id)->first(['prefix', 'start_form']);
            $data['fees_assigneds'] = $student->fees;
            $data['fees_discounts'] = $fees_discounts;
            $data['applied_discount'] = $applied_discount;
            if (moduleStatusCheck('University')) {
                $data['feesInstallments'] = UnFeesInstallmentAssign::where('un_academic_id', $student->un_academic_id)->where('un_semester_label_id', $student->un_semester_label_id)->where('record_id', $student->id)->get();
            } elseif (directFees()) {
                $data['feesInstallments'] = DirectFeesInstallmentAssign::where('academic_id', getAcademicId())->with('payments')->where('record_id', $student->id)->get();
            }


            return view('backEnd.feesCollection.collect_fees_student_wise', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function collectFeesStudentApi(Request $request, $id)
    {
        try {
            $student = AramiscStudent::where('user_id', $id)->where('school_id', Auth::user()->school_id)->first();
            $fees_assigneds = AramiscFeesAssign::where('student_id', $id)->orderBy('id', 'desc')->where('school_id', Auth::user()->school_id)->get();

            $fees_assigneds2 = DB::table('aramisc_fees_assigns')
                ->select('aramisc_fees_types.id as fees_type_id', 'aramisc_fees_types.name', 'aramisc_fees_masters.date as due_date', 'aramisc_fees_masters.amount as amount')
                ->join('aramisc_fees_masters', 'aramisc_fees_masters.id', '=', 'aramisc_fees_assigns.fees_master_id')
                ->join('aramisc_fees_types', 'aramisc_fees_types.id', '=', 'aramisc_fees_masters.fees_type_id')
                // ->join('aramisc_fees_payments', 'aramisc_fees_payments.fees_type_id', '=', 'aramisc_fees_masters.fees_type_id')
                ->where('aramisc_fees_assigns.student_id', $student->id)
                ->where('aramisc_fees_assigns.school_id', Auth::user()->school_id)->get();

            // return $fees_assigneds2;
            $i = 0;
            $d = [];
            foreach ($fees_assigneds2 as $row) {
                $d[$i]['fees_type_id'] = $row->fees_type_id;
                $d[$i]['fees_name'] = $row->name;
                $d[$i]['due_date'] = $row->due_date;
                $d[$i]['amount'] = $row->amount;
                $d[$i]['paid'] = DB::table('aramisc_fees_payments')->where('fees_type_id', $row->fees_type_id)->where('student_id', $student->id)->sum('amount');
                $d[$i]['fine'] = DB::table('aramisc_fees_payments')->where('fees_type_id', $row->fees_type_id)->where('student_id', $student->id)->sum('fine');
                $d[$i]['discount_amount'] = DB::table('aramisc_fees_payments')->where('fees_type_id', $row->fees_type_id)->where('student_id', $student->id)->sum('discount_amount');
                $d[$i]['balance'] = ((float) $d[$i]['amount'] + (float) $d[$i]['fine'])  - ((float) $d[$i]['paid'] + (float) $d[$i]['discount_amount']);
                $i++;
            }

            //, DB::raw("SUM(aramisc_fees_payments.amount) as total_paid where aramisc_fees_payments.fees_type_id==")
            $fees_discounts = AramiscFeesAssignDiscount::where('student_id', $id)->where('school_id', Auth::user()->school_id)->get();

            $applied_discount = [];
            foreach ($fees_discounts as $fees_discount) {
                $fees_payment = AramiscFeesPayment::select('fees_discount_id')->where('active_status', 1)->where('fees_discount_id', $fees_discount->id)->where('school_id', Auth::user()->school_id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }

            $currency_symbol = AramiscGeneralSettings::select('currency_symbol')->where('school_id', Auth::user()->school_id)->first();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                // $data['student'] = $student;
                $data['fees'] = $d;
                $data['currency_symbol'] = $currency_symbol;
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.collect_fees_student_wise', compact('student', 'fees_assigneds', 'fees_discounts', 'applied_discount'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
