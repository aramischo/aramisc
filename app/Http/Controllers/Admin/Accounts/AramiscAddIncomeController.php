<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\AramiscAddIncome;
use App\AramiscBankAccount;
use App\AramiscBankStatement;
use App\AramiscChartOfAccount;
use App\AramiscPaymentMethhod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Accounts\AramiscAddIncomeRequest;

class AramiscAddIncomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
    }

    public function index(Request $request)
    {
        try {
            $add_incomes = AramiscAddIncome::with('paymentMethod','ACHead')->get();
            $income_heads = AramiscChartOfAccount::where('type', "I")->get();
            $bank_accounts = AramiscBankAccount::get();
            $payment_methods = AramiscPaymentMethhod::get();
            return view('backEnd.accounts.add_income', compact('add_incomes', 'income_heads', 'bank_accounts', 'payment_methods'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function store(AramiscAddIncomeRequest $request)
    {
        try {
            $destination='public/uploads/add_income/';
           // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $add_income = new AramiscAddIncome();
            $add_income->name = $request->name;
            $add_income->income_head_id = $request->income_head;
            $add_income->date =date('Y-m-d',strtotime($request->date));
            $add_income->payment_method_id = $request->payment_method;
            if (paymentMethodName($request->payment_method)) {
                $add_income->account_id = $request->accounts;
            }
            $add_income->amount = $request->amount;
            $add_income->file = fileUpload($request->file,$destination);
            $add_income->description = $request->description;
            $add_income->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $add_income->un_academic_id = getAcademicId();
            }else{
                $add_income->academic_id = getAcademicId();
            }
            $add_income->save();

            if(paymentMethodName($request->payment_method)){
                $bank=AramiscBankAccount::where('id',$request->accounts)->first();
                $after_balance= $bank->current_balance + $request->amount;

                $bank_statement= new AramiscBankStatement();
                $bank_statement->amount= $request->amount;
                $bank_statement->after_balance= $after_balance;
                $bank_statement->type= 1;
                $bank_statement->details= $request->name;
                $bank_statement->item_sell_id= $add_income->id;
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
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $add_income = AramiscAddIncome::find($id);            
            $add_incomes = AramiscAddIncome::get();
            $income_heads = AramiscChartOfAccount::get();
            $bank_accounts = AramiscBankAccount::get();
            $payment_methods = AramiscPaymentMethhod::get();
            return view('backEnd.accounts.add_income', compact('add_income', 'add_incomes', 'income_heads', 'bank_accounts', 'payment_methods'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(AramiscAddIncomeRequest $request)
    {
        try {
            $destination =  'public/uploads/add_income/'; 
           // DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $add_income = AramiscAddIncome::find($request->id);            
            $add_income->name = $request->name;
            $add_income->income_head_id = $request->income_head;
            $add_income->date = date('Y-m-d', strtotime($request->date));
            $add_income->payment_method_id = $request->payment_method;
            if (paymentMethodName($request->payment_method)) {
                $add_income->account_id = $request->accounts;
            }
            $add_income->amount = $request->amount;
            $add_income->file = fileUpdate($add_income->file,$request->file,$destination);
            $add_income->description = $request->description;
            $add_income->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $add_income->un_academic_id = getAcademicId();
            }else{
                $add_income->academic_id = getAcademicId();
            }
            $add_income->save();

            if(paymentMethodName($request->payment_method)){
                AramiscBankStatement::where('item_sell_id', $request->id)->delete();
                $bank=AramiscBankAccount::where('id',$request->accounts)->first();
                $after_balance= $bank->current_balance + $request->amount;

                $bank_statement= new AramiscBankStatement();
                $bank_statement->amount= $request->amount;
                $bank_statement->after_balance= $after_balance;
                $bank_statement->type= 1;
                $bank_statement->details= $request->name;
                $bank_statement->item_sell_id= $add_income->id;
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
            return redirect()->route('add_income');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function delete(Request $request)
    {
        try {
          
            $add_income = AramiscAddIncome::find($request->id);
            if ($add_income->file != "") {
                $path = $add_income->file;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
           // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            if(paymentMethodName($add_income->payment_method_id) && $add_income->account_id){
                $reset_balance = AramiscBankStatement::where('item_sell_id',$request->id)->sum('amount');
                $bank=AramiscBankAccount::where('id',$add_income->account_id)->first();
                $after_balance= $bank->current_balance - $reset_balance;

                $current_balance= AramiscBankAccount::find($add_income->account_id);
                $current_balance->current_balance=$after_balance;
                $current_balance->update();
                AramiscBankStatement::where('item_sell_id',$request->id)->delete();
            }
            $add_income->delete();

            Toastr::success('Operation successful', 'Success');
            return redirect()->route('add_income');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}