<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\AramiscAddExpense;
use App\ApiBaseMethod;
use App\AramiscBankAccount;
use App\AramiscBankStatement;
use App\AramiscChartOfAccount;
use App\AramiscPaymentMethhod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Accounts\SmExpenseRequest;

class AramiscAddExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
    }

    public function index(Request $request)
    {
        try {
            $add_expenses    = AramiscAddExpense::with('expenseHead','ACHead','paymentMethod','account')->get();
            $expense_heads   = AramiscChartOfAccount::where('type', "E")->get(['head','id']);
            $bank_accounts   = AramiscBankAccount::get();
            $payment_methods = AramiscPaymentMethhod::get(['method','id']);
            return view('backEnd.accounts.add_expense', compact('add_expenses', 'expense_heads', 'bank_accounts', 'payment_methods'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function store(SmExpenseRequest $request)
    {
        try {
            $destination='public/uploads/addExpense/';
           // DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $add_expense = new AramiscAddExpense();
            $add_expense->name = $request->name;
            $add_expense->expense_head_id = $request->expense_head;
            $add_expense->date = date('Y-m-d', strtotime($request->date));
            $add_expense->payment_method_id = $request->payment_method;
            if (paymentMethodName($request->payment_method)) {
                $add_expense->account_id = $request->accounts;
            }
            $add_expense->amount = $request->amount;
            $add_expense->file = fileUpload($request->file,$destination);
            $add_expense->description = $request->description;
            $add_expense->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $add_expense->un_academic_id = getAcademicId();
            }else{
                $add_expense->academic_id = getAcademicId();
            }
            $result = $add_expense->save();

            if(paymentMethodName($request->payment_method)){
                $bank=AramiscBankAccount::where('id',$request->accounts)
                ->where('school_id',Auth::user()->school_id)
                ->first();
                $after_balance= $bank->current_balance - $request->amount;

                $bank_statement= new AramiscBankStatement();
                $bank_statement->amount= $request->amount;
                $bank_statement->after_balance= $after_balance;
                $bank_statement->type= 0;
                $bank_statement->details= $request->name;
                $bank_statement->item_receive_id= $add_expense->id;
                $bank_statement->payment_date= date('Y-m-d',strtotime($request->date));
                $bank_statement->bank_id= $request->accounts;
                $bank_statement->school_id=Auth::user()->school_id;
                $bank_statement->payment_method= $request->payment_method;
                $bank_statement->save();

                $current_balance= AramiscBankAccount::find($request->accounts);
                $current_balance->current_balance=$after_balance;
                $current_balance->update();
            }

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    
    public function show(Request $request, $id)
    {
        try {
            $add_expense = AramiscAddExpense::find($id);
            $add_expenses    = AramiscAddExpense::with('expenseHead','ACHead','paymentMethod','account')->get();
            $expense_heads   = AramiscChartOfAccount::where('type', "E")->get(['head','id']);
            $bank_accounts   = AramiscBankAccount::get();
            $payment_methods = AramiscPaymentMethhod::get(['method','id']);
            return view('backEnd.accounts.add_expense', compact('add_expenses', 'add_expense', 'expense_heads', 'bank_accounts', 'payment_methods'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(SmExpenseRequest $request, $id)
    {
        try {
            $destination =  'public/uploads/addExpense/';
           // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
             if (checkAdmin()) {
                    $add_expense = AramiscAddExpense::find($request->id);
                }else{
                    $add_expense = AramiscAddExpense::where('id',$request->id)->where('school_id',Auth::user()->school_id)->first();
            }
            $add_expense->name = $request->name;
            $add_expense->expense_head_id = $request->expense_head;
            $add_expense->date = date('Y-m-d', strtotime($request->date));
            $add_expense->payment_method_id = $request->payment_method;
            if (paymentMethodName($request->payment_method)) {
                $add_expense->account_id = $request->accounts;
            }
            $add_expense->amount = $request->amount;
            $add_expense->file = fileUpdate($add_expense->file,$request->file,$destination);
            $add_expense->school_id = Auth::user()->school_id;
            $add_expense->description = $request->description;
            if(moduleStatusCheck('University')){
                $add_expense->un_academic_id = getAcademicId();
            }else{
                $add_expense->academic_id = getAcademicId();
            }
            $result = $add_expense->save();

            if(paymentMethodName($request->payment_method)){
                AramiscBankStatement::where('item_receive_id', $request->id)
                                    ->where('school_id',Auth::user()->school_id)
                                    ->delete();
                $bank=AramiscBankAccount::where('id',$request->accounts)
                                ->where('school_id',Auth::user()->school_id)
                                ->first();
                $after_balance= $bank->current_balance - $request->amount;

                $bank_statement= new AramiscBankStatement();
                $bank_statement->amount= $request->amount;
                $bank_statement->after_balance= $after_balance;
                $bank_statement->type= 0;
                $bank_statement->details= $request->name;
                $bank_statement->item_receive_id= $add_expense->id;
                $bank_statement->payment_date= date('Y-m-d',strtotime($request->date));
                $bank_statement->bank_id= $request->accounts;
                $bank_statement->school_id= Auth::user()->school_id;
                $bank_statement->payment_method= $request->payment_method;
                $bank_statement->save();

                $current_balance= AramiscBankAccount::find($request->accounts);
                $current_balance->current_balance=$after_balance;
                $current_balance->update();
            }

            Toastr::success('Operation successful', 'Success');
            return redirect('add-expense');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function destroy(Request $request)
    {
        try {
            $id = $request->id;
            $add_expense = AramiscAddExpense::find($id);
            if ($add_expense->file != "") {
                unlink($add_expense->file);
            }
            if(paymentMethodName($add_expense->payment_method_id)){
                $reset_balance = AramiscBankStatement::where('item_receive_id',$add_expense->account_id)
                                ->where('school_id',Auth::user()->school_id)
                                ->sum('amount');

                $bank=AramiscBankAccount::where('id',$add_expense->account_id)
                                ->where('school_id',Auth::user()->school_id)
                                ->first();
                $after_balance= $bank->current_balance + $add_expense->amount;

                $current_balance= AramiscBankAccount::find($add_expense->account_id);
                $current_balance->current_balance=$after_balance;
                $current_balance->update();

                AramiscBankStatement::where('item_receive_id',$id)
                                ->where('school_id',Auth::user()->school_id)
                                ->delete();
            }
            $add_expense->delete();

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}