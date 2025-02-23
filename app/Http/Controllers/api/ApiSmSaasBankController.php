<?php

namespace App\Http\Controllers\api;

use App\Scopes\StatusAcademicSchoolScope;
use App\User;
use App\AramiscStudent;
use App\ApiBaseMethod;
use App\AramiscBankAccount;
use App\AramiscAcademicYear;
use App\AramiscBookCategory;
use App\AramiscNotification;
use App\AramiscPaymentMethhod;
use App\AramiscBankPaymentSlip;
use Illuminate\Http\Request;
use App\AramiscTeacherUploadContent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\StudentRecord;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;

class ApiSmSaasBankController extends Controller
{
    public function saas_bankList(Request $request,$school_id){
        try {
             $banks=AramiscBankAccount::where('active_status',1)
                            ->where('academic_id', AramiscAcademicYear::API_ACADEMIC_YEAR($school_id))
                            ->where('school_id',$school_id)->get(['id','bank_name','account_name','account_number']);
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['banks'] = $banks->toArray();           
            return ApiBaseMethod::sendResponse($data, null);
        }
        } catch (\Throwable $th) {
            
        }
       
    }
    public function saas_childBankSlipStore(Request $request)
    {
        if(ApiBaseMethod::checkUrl($request->fullUrl())){
            $input = $request->all();
            $validator = Validator::make($input, [
          
            'amount'=> "required",
            'class_id' =>"required",
            'section_id'=>"required",
            'user_id'=>"required",
            'fees_type_id'=>"required",
            'payment_mode'=>"required",
            'date'=>"required",
            'school_id'=>"required",

           
        ]);
        }
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
         }

        try {

            if($request->payment_mode=="bank"){
                if($request->bank_id==''){                  
                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        return ApiBaseMethod::sendError('Bank Field Required');
                    }
                }
            }


            $fileName = "";
            if ($request->file('slip') != "") {
                $file = $request->file('slip');
                $fileName = $request->input('user_id') . time() . "." . $file->getClientOriginalExtension();                
                $file->move('public/uploads/bankSlip/',$fileName);
                $fileName = 'public/uploads/bankSlip/' . $fileName;
            }

            $student=AramiscStudent::where('user_id',$request->user_id)->first();

            $date = strtotime($request->date);
            $newformat = date('Y-m-d', $date);
            $payment_mode_name=ucwords($request->payment_mode);
            $payment_method=AramiscPaymentMethhod::where('method',$payment_mode_name)->first();

            $payment = new AramiscBankPaymentSlip();
            $payment->date = $newformat;
            $payment->amount = $request->amount;
            $payment->note = $request->note;
            $payment->slip = $fileName;
            $payment->fees_type_id = $request->fees_type_id;
            $payment->student_id = $student->id;
            $payment->payment_mode = $request->payment_mode;
            if($payment_method->id==3){
                $payment->bank_id = $request->bank_id;
            }
            $payment->class_id = $request->class_id;
            $payment->section_id = $request->section_id;
            $payment->school_id = $request->school_id;
            $payment->academic_id = AramiscAcademicYear::API_ACADEMIC_YEAR($request->school_id);
            $result=$payment->save();

            if($result){
                $users = User::whereIn('role_id',[1,5])->where('school_id', 1)->get();
                foreach($users as $user){
                    $notification = new AramiscNotification();
                    $notification->message = $student->full_name .'Payment Recieve';
                    $notification->is_read = 0;
                    $notification->url = "bank-payment-slip";
                    $notification->user_id = $user->id;
                    $notification->role_id = $user->role_id;
                    $notification->school_id = $request->school_id;
                    $notification->academic_id = $student->academic_id;
                    $notification->date = date('Y-m-d');
                    $notification->save();
                }
            }


          if(ApiBaseMethod::checkUrl($request->fullUrl())){
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Payment Added, Please Wait for approval');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            }
         
        } catch (\Exception $e) {

        }
    }





    public function saas_roomList(Request $request)
    {
        $studentDormitory = DB::table('aramisc_room_lists')
            ->join('aramisc_dormitory_lists', 'aramisc_room_lists.dormitory_id', '=', 'aramisc_dormitory_lists.id')
            ->join('aramisc_room_types', 'aramisc_room_lists.room_type_id', '=', 'aramisc_room_types.id')
            ->select('aramisc_room_lists.id', 'aramisc_dormitory_lists.dormitory_name', 'aramisc_room_lists.name as room_number', 'aramisc_room_lists.number_of_bed', 'aramisc_room_lists.cost_per_bed', 'aramisc_room_lists.active_status')
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($studentDormitory, null);
        }
    }

    
    public function saas_bookCategory(Request $request, $school_id)
    {
        $book_category = DB::table('aramisc_book_categories')->where('school_id',$school_id)->get();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($book_category, null);
        }
    }
    public function saas_bookCategoryStore(Request $request)
    {
                $input = $request->all();
            $validator = Validator::make($input, [
            'category_name'=>"required|max:200|unique:aramisc_book_categories,category_name",
            'school_id'=>"required",
        ]);
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
         }
        try{
            $categories = new AramiscBookCategory();
            $categories->category_name = $request->category_name;
            $categories->school_id = $request->school_id;          
            $results = $categories->save();

           
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if($results){
                         return ApiBaseMethod::sendResponse(null, 'Book Category has been created successfully');
                    }else{
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                }
           
        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }
}
