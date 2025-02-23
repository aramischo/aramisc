<?php

namespace App\Http\Controllers;

use App\User;
use App\AramiscClass;
use App\AramiscParent;
use App\AramiscStudent;
use App\tableList;
use App\YearCheck;
use Carbon\Carbon;
use App\AramiscFeesType;
use App\AramiscBaseSetup;
use App\AramiscFeesGroup;
use App\AramiscFeesAssign;
use App\AramiscFeesMaster;
use App\ApiBaseMethod;
use App\AramiscFeesPayment;
use App\AramiscNotification;
use App\AramiscStudentGroup;
use App\AramiscStudentCategory;
use App\AramiscFeesCarryForward;
use Illuminate\Http\Request;
use App\AramiscFeesAssignDiscount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\FeesAssignNotification;

class AramiscFeesMasterController extends Controller

{
    public function __construct()
    {
       // $this->middleware('PM');
        // User::checkAuth();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $fees_groups = AramiscFeesGroup::where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();
            $fees_masters = AramiscFeesMaster::where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();
            $already_assigned = [];
            foreach ($fees_masters as $fees_master) {
                $already_assigned[] = $fees_master->fees_type_id;
            }
            $fees_masters = $fees_masters->groupBy('fees_group_id');
            $fees_types = AramiscFeesType::where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();

            return view('backEnd.feesCollection.fees_master', compact('fees_groups', 'fees_types', 'fees_masters', 'already_assigned'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function store(Request $request)
    {
        // return $request;
        $request->validate([
            'fees_type' => "required",
            'date' => "required",
            'amount' => "required|integer|min:0"
        ]);
        try {
            $fees_type = AramiscFeesType::find($request->fees_type);
            $combination = AramiscFeesMaster::where('fees_group_id', $request->fees_group)->where('fees_type_id', $request->fees_type)->count();
            if ($combination == 0) {
                $fees_master = new AramiscFeesMaster();
                $fees_master->fees_group_id = $fees_type->fees_group_id;
                $fees_master->fees_type_id = $request->fees_type;
                $fees_master->date = date('Y-m-d', strtotime($request->date));
                $fees_master->school_id = Auth::user()->school_id;
                $fees_master->academic_id = getAcademicId();
                $fees_master->amount = $request->amount;
                $result = $fees_master->save();
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            } elseif ($combination == 1) {
                Toastr::error('Already fees assigned', 'Failed');
                return redirect()->back();
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        try {
            if (checkAdmin()) {
                $fees_master = AramiscFeesMaster::find($id);
            }else{
                $fees_master = AramiscFeesMaster::where('id',$id)->where('school_id',Auth::user()->school_id)->first();
            }
            $fees_groups = AramiscFeesGroup::where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();
            $fees_types = AramiscFeesType::where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();
            $fees_masters = AramiscFeesMaster::where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();

            $already_assigned = [];
            foreach ($fees_masters as $master) {
                if ($fees_master->fees_type_id != $master->fees_type_id) {
                    $already_assigned[] = $master->fees_type_id;
                }
            }

            $fees_masters = $fees_masters->distinct('fees_group_id');
            return view('backEnd.feesCollection.fees_master', compact('fees_groups', 'fees_types', 'fees_master', 'fees_masters', 'already_assigned'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'fees_type' => "required",
            'amount' => "required"
        ]);

        try {
            $fees_type = AramiscFeesType::find($request->fees_type);
            if (checkAdmin()) {
                $fees_master = AramiscFeesMaster::find($request->id);
            }else{
                $fees_master = AramiscFeesMaster::where('id',$request->id)->where('school_id',Auth::user()->school_id)->first();
            }
            $fees_master->fees_type_id = $request->fees_type;
            $fees_master->date = date('Y-m-d', strtotime($request->date));
            $fees_master->amount = $request->amount;
            $fees_master->fees_group_id = $fees_type->fees_group_id;
            $result = $fees_master->save();
            if ($result) {
                Toastr::success('Operation successful', 'Success');
                return redirect('fees-master');
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        try {
            if (checkAdmin()) {
                $fees_master = AramiscFeesMaster::destroy($id);
            }else{
                $fees_master = AramiscFeesMaster::where('id',$id)->where('school_id',Auth::user()->school_id)->delete();
            }
            if ($fees_master) {
                Toastr::success('Operation successful', 'Success');
                return redirect('fees-master');
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteSingle(Request $request)
    {
        try {
            $id_key = 'fees_master_id';
            $tables = tableList::getTableList($id_key, $request->id);
            try {
                if ($tables == null) {
                    $check_fees_assign = AramiscFeesAssign::where('fees_master_id', $request->id)
                    ->where('school_id',Auth::user()->school_id)
                        ->join('aramisc_students','aramisc_students.id','=','aramisc_fees_assigns.student_id')->first();
                    if ($check_fees_assign != null) {
                        $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                        Toastr::error($msg, 'Failed');
                        return redirect()->back();
                    }
                    if (checkAdmin()) {
                        $delete_query = AramiscFeesMaster::destroy($request->id);
                    }else{
                        $delete_query = AramiscFeesMaster::where('id',$request->id)->where('school_id',Auth::user()->school_id)->delete();
                    }
                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        if ($delete_query) {
                            return ApiBaseMethod::sendResponse(null, 'Fees Master has been deleted successfully');
                        } else {
                            return ApiBaseMethod::sendError('Something went wrong, please try again.');
                        }
                    } else {
                        if ($delete_query) {
                            Toastr::success('Operation successful', 'Success');
                            return redirect()->back();
                        } else {
                            Toastr::error('Operation Failed', 'Failed');
                            return redirect()->back();
                        }
                    }
                } else {
                    $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
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

    public function deleteGroup(Request $request)
    {
        try {
            $id_key = 'fees_group_id';
            $tables = tableList::getTableList($id_key, $request->id);
            try {
                $assigned_master_id=[];
                $fees_group_master=AramiscFeesAssign::where('school_id',Auth::user()->school_id)->get();
                foreach ($fees_group_master as $key => $value) {
                    $assigned_master_id[]=$value->fees_master_id;
                }
                $feesmasters = AramiscFeesMaster::where('fees_group_id',$request->id)->get();
                foreach ($feesmasters as $feesmaster) {
                    if (!in_array($feesmaster->id, $assigned_master_id)) {
                        if (checkAdmin()) {
                            $delete_query = AramiscFeesMaster::destroy($feesmaster->id);
                        }else{
                            $delete_query = AramiscFeesMaster::where('id',$feesmaster->id)->where('school_id',Auth::user()->school_id)->delete();
                        }
                    }else{
                        $msg = 'This data already used in : ' . $tables . ' Please remove those data first';
                        Toastr::error($msg, 'Failed');
                        return redirect()->back();
                    }

                }
                if ($delete_query) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function feesAssign(Request $request, $id)
    {
        try {
            $fees_group_id = $id;
            $classes = AramiscClass::where('active_status', 1)->where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();
            $groups = AramiscStudentGroup::where('active_status', '=', '1')->where('school_id', Auth::user()->school_id)->get();
            $categories = AramiscStudentCategory::where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['categories'] = $categories->toArray();
                $data['groups'] = $groups->toArray();
                $data['fees_group_id'] = $fees_group_id;
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.feesCollection.fees_assign', compact('classes', 'categories', 'groups', 'fees_group_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function feesAssignSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => "required"
        ]);
// return $request;
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $section_id=0;
            $classes = DB::table('aramisc_classes')->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $groups = DB::table('aramisc_student_groups')->where('active_status', '=', '1')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $categories = DB::table('aramisc_student_categories')->where('school_id', Auth::user()->school_id)->get();
            $fees_group_id = $request->fees_group_id;

            $students = AramiscStudent::query();
            $students->where('active_status', 1);
            if ($request->class != "") {
                $students->where('class_id', $request->class);
            }
            if ($request->section != "") {
                $students->where('section_id', $request->section);
                $section_id=$request->section;
            }
            if ($request->category != "") {
                $students->where('student_category_id', $request->category);
            }
            if ($request->group != "") {
                $students->where('student_group_id', $request->group);
            }

            $students = $students->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $fees_masters = AramiscFeesMaster::where('fees_group_id', $request->fees_group_id)->where('school_id', Auth::user()->school_id)->get();

            $pre_assigned = [];


            foreach ($students as $student) {
                foreach ($fees_masters as $fees_master) {
                    $assigned_student = AramiscFeesAssign::select('student_id')->where('student_id', $student->id)->where('fees_master_id', $fees_master->id)->where('school_id',Auth::user()->school_id)->first();

                    if ($assigned_student != "") {
                        if (!in_array($assigned_student->student_id, $pre_assigned)) {
                            $pre_assigned[] = $assigned_student->student_id;
                        }
                    }
                }

                
            }
            if ($pre_assigned !=null) {
                $assigned_value=1;
            } else {
                $assigned_value=0;
            }
            $class_id = $request->class;
            $section_id=$request->section;
            $category_id = $request->category;
            $group_id = $request->group;

            $fees_assign_groups = AramiscFeesMaster::where('fees_group_id', $request->fees_group_id)->where('school_id', Auth::user()->school_id)->get();

          
            // return $request;
            return view('backEnd.feesCollection.fees_assign', compact('classes', 'categories', 'groups', 'students', 'fees_assign_groups', 'fees_group_id', 'pre_assigned', 'class_id', 'category_id', 'group_id','assigned_value','section_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function feesAssignStore(Request $request)
    {
    //    return $request;
        try {
            $fees_masters = AramiscFeesMaster::where('fees_group_id', $request->fees_group_id)
                ->where('school_id', Auth::user()->school_id)
                ->get();
            if ($request->checked_ids != "") {
                foreach ($request->students as $student) {
                    foreach ($fees_masters as $fees_master) {
                        $payment_info=AramiscFeesPayment::where('active_status',1)->where('fees_type_id',$fees_master->fees_type_id)->where('student_id',$student)->first();
                        if ($payment_info==null) {
                            $assign_fees = AramiscFeesAssign::where('fees_master_id', $fees_master->id)->where('student_id', $student)->where('school_id',Auth::user()->school_id)->delete();
                        }
                    }
                }
            }
            if (!isset($request->checked_ids)) {
                foreach ($request->students as $student) {
                
                    foreach ($fees_masters as $fees_master) {
                        $payment_info=AramiscFeesPayment::where('active_status',1)->where('fees_type_id',$fees_master->fees_type_id)->where('student_id',$student)->first();
                        if ($payment_info==null) {
                            $assign_fees = AramiscFeesAssign::where('fees_master_id', $fees_master->id)->where('student_id', $student)->where('school_id',Auth::user()->school_id)->delete();
                        }
                    }
                }
            }
            if ($request->checked_ids != "") {
                foreach ($request->checked_ids as $student) {

                    foreach ($fees_masters as $fees_master) {
                        $assign_fees = AramiscFeesAssign::where('fees_master_id', $fees_master->id)->where('student_id', $student)->where('school_id',Auth::user()->school_id)->first();

                        if ( $assign_fees) {
                            continue;
                        }
                        $assign_fees = new AramiscFeesAssign();
                        $assign_fees->student_id = $student;
                        $assign_fees->fees_amount = $fees_master->amount;
                        $assign_fees->fees_master_id = $fees_master->id;
                        $assign_fees->school_id = Auth::user()->school_id;
                        $assign_fees->academic_id = getAcademicId();
                        $assign_fees->save();


                        //Yearly Discount assign

                        $check_yearly_discount=AramiscFeesAssignDiscount::where('fees_group_id',$request->fees_group_id)->where('student_id',$student)->where('school_id',Auth::user()->school_id)->first();

                        if ($check_yearly_discount) {
                            if ($assign_fees->fees_amount>$check_yearly_discount->applied_amount) {
                            
                                $payable_fees=$assign_fees->fees_amount-$check_yearly_discount->applied_amount;

                                $assign_fees->applied_discount=$check_yearly_discount->applied_amount;
                                $assign_fees->fees_discount_id = $check_yearly_discount->fees_discount_id;
                                $assign_fees->fees_amount = $payable_fees;
                                $assign_fees->save();
                            }
                           
                        }
                    }

                    

                    //fees carry forward 

                    $forward = AramiscFeesCarryForward::where('student_id', $student)->first();
                    if($forward){
                       $forwardAmount = $forward->balance;
                    

                    if( (! is_null($forwardAmount)) ){                    
                        $students_info = AramiscStudent::find($student);
                        if($forwardAmount > 0 &&  $assign_fees->fees_amount >= $forwardAmount){
                            $fees_payment = new AramiscFeesPayment();
                            $fees_payment->student_id = $student;
                            $fees_payment->fees_type_id = $fees_master->fees_type_id;
                            $fees_payment->discount_amount = 0;
                            $fees_payment->fine = 0;
                            $fees_payment->amount = $forwardAmount;
                            $fees_payment->payment_date = date('Y-m-d');
                            $fees_payment->payment_mode = @$forward->notes;
                            $fees_payment->created_by = Auth::id();
                            $fees_payment->note = @$forward->notes;
                            $fees_payment->academic_id = getAcademicId();
                            $fees_payment->school_id = Auth::user()->school_id;
                            $result =  $fees_payment->save();
                            if($result){
                                $forwardAmount = 0 ;
                                $fees_balance = AramiscFeesCarryForward::where('student_id', $student)->first();
                                $fees_balance->balance = $forwardAmount;
                                $fees_balance->save();
                            }
                        }
                        elseif($forwardAmount > 0 &&  $fees_master->amount < $forwardAmount){
                            $fees_payment = new AramiscFeesPayment();
                            $fees_payment->student_id = $student;
                            $fees_payment->fees_type_id = $fees_master->fees_type_id;
                            $fees_payment->discount_amount = 0;
                            $fees_payment->fine = 0;
                            $fees_payment->amount = $fees_master->amount;
                            $fees_payment->payment_date = date('Y-m-d');
                            $fees_payment->payment_mode = @$forward->notes;
                            $fees_payment->created_by = Auth::id();
                            $fees_payment->note = @$forward->notes;
                            $fees_payment->academic_id = getAcademicId();
                            $fees_payment->school_id = Auth::user()->school_id;
                            $result =  $fees_payment->save();
                            if($result){
                                $forwardAmount = $forwardAmount- $fees_master->amount;
                                $fees_balance = AramiscFeesCarryForward::where('student_id', $student)->first();
                                $fees_balance->balance = $forwardAmount;
                                $fees_balance->save();
                            }

                        }
                        elseif($forwardAmount < 0){
                            $fees_payment = new AramiscFeesPayment();
                            $fees_payment->student_id = $student;
                            $fees_payment->fees_type_id = $fees_master->fees_type_id;
                            $fees_payment->discount_amount = 0;
                            $fees_payment->fine = 0;
                            $fees_payment->amount = $forwardAmount;
                            $fees_payment->payment_date = date('Y-m-d');
                            $fees_payment->payment_mode = @$forward->notes;
                            $fees_payment->created_by = Auth::id();
                            $fees_payment->note = @$forward->notes;
                            $fees_payment->academic_id = getAcademicId();
                            $fees_payment->school_id = Auth::user()->school_id;
                            $result =  $fees_payment->save();
                            if($result){
                                $forwardAmount = 0 ;
                                $fees_balance = AramiscFeesCarryForward::where('student_id', $student)->first();
                                $fees_balance->balance = $forwardAmount;
                                $fees_balance->save();
                            }

                        }
                    }
                    }
                    $students_info = AramiscStudent::find($student);
                    $notification = new AramiscNotification;
                    $notification->user_id = $students_info->user_id;
                    $notification->role_id = 2;
                    $notification->date = date('Y-m-d');
                    $notification->message = app('translator')->get('fees.fees_assigned');
                    $notification->school_id = Auth::user()->school_id;
                    $notification->academic_id = getAcademicId();
                    $notification->save();
                    
                    try{
                        $user=User::find($students_info->user_id);
                        Notification::send($user, new FeesAssignNotification($notification));
                    }catch (\Exception $e) {
                        Log::info($e->getMessage());
                    }
    
                    $parent = AramiscParent::find($students_info->parent_id);
                    $notification2 = new AramiscNotification;
                    $notification2->user_id = $parent->user_id;
                    $notification2->role_id = 3;
                    $notification2->date = date('Y-m-d');
                    $notification2->message = app('translator')->get('fees.fees_assigned_for').' '. $students_info->full_name;
                    $notification2->school_id = Auth::user()->school_id;
                    $notification2->academic_id = getAcademicId();
                    $notification2->save();

                    try{
                        $user=User::find($parent->user_id);
                        Notification::send($user, new FeesAssignNotification($notification2));
                    }catch (\Exception $e) {
                        Log::info($e->getMessage());
                    }
                }
            }

            $html = "";
            return response()->json([$html]);

        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }
    public function feesAssignStoreOld(Request $request)
    {
        try {
            
           
            $fees_masters = AramiscFeesMaster::where('fees_group_id', $request->fees_group_id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            if ($request->checked_ids != "") {

                $all_students=($request->students);
                $check=($request->checked_ids);

                 $student_ids =array_diff($all_students,$check);

                foreach ($student_ids as $student) {
                    foreach ($fees_masters as $fees_master) {
                        $assign_fees = AramiscFeesAssign::where('fees_master_id', $fees_master->id)
                        ->where('student_id', $student)
                        ->where('school_id',Auth::user()->school_id)           
                        ->delete();
                    }
                }
            }    

            
            if ($request->checked_ids != "") {
                foreach ($request->checked_ids as $student) {
                    foreach ($fees_masters as $fees_master) {
                        $assign_fees = AramiscFeesAssign::where('fees_master_id', $fees_master->id)->where('student_id', $student)->where('school_id',Auth::user()->school_id)->first();

                        if ( $assign_fees) {
                            continue;
                        }
                        $assign_fees = new AramiscFeesAssign();
                        $assign_fees->student_id = $student;
                        $assign_fees->fees_amount = $fees_master->amount;
                        $assign_fees->fees_master_id = $fees_master->id;                     
                        $assign_fees->school_id = Auth::user()->school_id;
                        $assign_fees->academic_id = getAcademicId();
                        $assign_fees->save();


                        //Yearly Discount assign

                        $check_yearly_discount=AramiscFeesAssignDiscount::where('fees_group_id',$request->fees_group_id)->where('student_id',$student)->where('school_id',Auth::user()->school_id)->first();

                        if ($check_yearly_discount) {
                            if ($assign_fees->fees_amount>$check_yearly_discount->applied_amount) {
                            
                                $payable_fees=$assign_fees->fees_amount-$check_yearly_discount->applied_amount;

                                $assign_fees->applied_discount=$check_yearly_discount->applied_amount;
                                $assign_fees->fees_discount_id = $check_yearly_discount->fees_discount_id;
                                $assign_fees->fees_amount = $payable_fees;
                                $assign_fees->save();
                            }
                           
                        }
                    }
                }
            }

            foreach ($request->students as $student) {
                $students_info = AramiscStudent::find($student);
                $notification = new AramiscNotification;
                $notification->user_id = $students_info->user_id;
                $notification->role_id = 2;
                $notification->date = date('Y-m-d');
                $notification->message = 'New fees Assigned';
                $notification->school_id = Auth::user()->school_id;
                $notification->academic_id = getAcademicId();
                $notification->save();

                $parent = AramiscParent::find($students_info->parent_id);
                $notification2 = new AramiscNotification;
                $notification2->user_id = $parent->user_id;
                $notification2->role_id = 3;
                $notification2->date = date('Y-m-d');
                $notification2->message = 'New fees Assigned For ' . $students_info->full_name;
                $notification2->school_id = Auth::user()->school_id;
                $notification2->academic_id = getAcademicId();
                $notification2->save();
            }
            $html = "";
            return response()->json([$html]);
        } catch (\Exception $e) {
            return response()->json("", 404);
        }
    }
}