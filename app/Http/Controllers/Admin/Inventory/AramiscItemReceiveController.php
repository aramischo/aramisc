<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\AramiscItem;
use App\AramiscItemSell;
use App\AramiscSupplier;
use App\AramiscItemStore;
use App\AramiscAddExpense;
use App\AramiscBankAccount;
use App\AramiscItemReceive;
use App\AramiscBankStatement;
use App\AramiscChartOfAccount;
use App\AramiscPaymentMethhod;
use App\AramiscGeneralSettings;
use App\AramiscInventoryPayment;
use App\AramiscItemReceiveChild;
use Illuminate\Http\Request;
use App\Traits\NotificationSend;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Http\Requests\Admin\Inventory\AramiscItemReceiveRequest;

class AramiscItemReceiveController extends Controller
{
    use NotificationSend;
    public function __construct()
    {
        $this->middleware('PM');
    }

    public function itemReceive()
    {
        try {
            $account_id = AramiscBankAccount::get();
            $expense_head = AramiscChartOfAccount::where('type', 'E')->get();
            $suppliers = AramiscSupplier::get();
            $itemStores = AramiscItemStore::where('school_id', Auth::user()->school_id)->get();
            $items = AramiscItem::with('category')->get();
            $paymentMethhods = AramiscPaymentMethhod::get();
            return view('backEnd.inventory.itemReceive', compact('suppliers', 'itemStores', 'items', 'paymentMethhods','account_id', 'expense_head'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function getReceiveItem()
    {
        try {
            $searchData = AramiscItem::where('school_id', Auth::user()->school_id)->get();
            if (!empty($searchData)) {
                return json_encode($searchData);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function saveItemReceiveData(AramiscItemReceiveRequest $request)
    {
       //   uest->all());
       // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        try {
            $total_paid = '';
            if (empty($request->totalPaidValue)) {
                $total_paid = $request->totalPaid;
            } else {
                $total_paid = $request->totalPaidValue;
            }
            $subTotalValue = round($request->subTotalValue);
            $totalDueValue = round($request->totalDueValue);
            $paid_status = '';
            if ($totalDueValue == 0) {
                $paid_status = 'P';
            } elseif ($subTotalValue == $totalDueValue) {
                $paid_status = 'U';
            } else {
                $paid_status = 'PP';
            }
            $itemReceives = new AramiscItemReceive();
            $itemReceives->supplier_id = $request->supplier_id;
            $itemReceives->store_id = $request->store_id;
            $itemReceives->reference_no = $request->reference_no;
            $itemReceives->receive_date = date('Y-m-d', strtotime($request->receive_date));
            $itemReceives->grand_total = $request->subTotalValue;
            $itemReceives->total_quantity = $request->subTotalQuantityValue;
            $itemReceives->total_paid = $total_paid;
            $itemReceives->paid_status = $paid_status;
            $itemReceives->total_due = $request->totalDueValue;
            $itemReceives->account_id = $request->bank_id;
            $itemReceives->expense_head_id = $request->expense_head_id;
            $itemReceives->payment_method = $request->payment_method;
            $itemReceives->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $itemReceives->un_academic_id = getAcademicId();
            }else{
                $itemReceives->academic_id = getAcademicId();
            }
            $results = $itemReceives->save();
            $itemReceives->toArray();

            $add_expense = new AramiscAddExpense();
            $add_expense->name = 'Item Receive';
            $add_expense->date = date('Y-m-d', strtotime($request->receive_date));
            $add_expense->amount = $total_paid;
            $add_expense->item_receive_id = $itemReceives->id;
            $add_expense->active_status = 1;
            $add_expense->expense_head_id = $request->expense_head_id;
            $add_expense->account_id = $request->bank_id;
            $add_expense->payment_method_id = $request->payment_method;
            $add_expense->created_by = Auth()->user()->id;
            $add_expense->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $add_expense->un_academic_id = getAcademicId();
            }else{
                $add_expense->academic_id = getAcademicId();
            }
            $add_expense->save();

            if(paymentMethodName($request->payment_method)){
                $bank=AramiscBankAccount::where('id',$request->bank_id)
                ->where('school_id',Auth::user()->school_id)
                ->first();
                $after_balance= $bank->current_balance - $total_paid;

                $bank_statement= new AramiscBankStatement();
                $bank_statement->amount= $total_paid;
                $bank_statement->after_balance= $after_balance;
                $bank_statement->type= 0;
                $bank_statement->details= "Item Receive Payment";
                $bank_statement->item_receive_id= $itemReceives->id;
                $bank_statement->payment_date= date('Y-m-d', strtotime($request->receive_date));
                $bank_statement->bank_id= $request->bank_id;
                $bank_statement->school_id=Auth::user()->school_id;
                $bank_statement->payment_method= $request->payment_method;
                $bank_statement->save();


                $current_balance= AramiscBankAccount::find($request->bank_id);
                $current_balance->current_balance=$after_balance;
                $current_balance->update();
            }
            $itemName = [];

            if ($results) {
                $item_ids = count($request->item_id);
                for ($i = 0; $i < $item_ids; $i++) {
                    if (!empty($request->item_id[$i])) {
                        $itemReceivedChild = new AramiscItemReceiveChild;
                        $itemReceivedChild->item_receive_id = $itemReceives->id;
                        $itemReceivedChild->item_id = $request->item_id[$i];
                        $itemReceivedChild->unit_price = $request->unit_price[$i];
                        $itemReceivedChild->quantity = $request->quantity[$i];
                        $itemReceivedChild->sub_total = $request->totalValue[$i];
                        $itemReceivedChild->created_by = Auth()->user()->id;
                        $itemReceivedChild->academic_id = getAcademicId();
                        $itemReceivedChild->school_id = Auth::user()->school_id;
                        $result = $itemReceivedChild->save();
                        $itemName [] = $itemReceivedChild->items->item_name;

                        if ($result) {
                            $items = AramiscItem::find($request->item_id[$i]);
                            $items->total_in_stock = $items->total_in_stock + $request->quantity[$i];
                            $results = $items->update();
                        }
                    }
                }
            } 

            $data['title'] = 'Item Receive';
            $data['grand_total'] = $request->subTotalValue;
            $data['total_paid'] = $total_paid;
            $data['total_due'] = $request->totalDueValue;
            $data['quantity'] = $request->subTotalQuantityValue;
            $data['item'] = implode(", ",$itemName);
            $this->sent_notifications('Item_Recieved', null, $data, null);

            Toastr::success('Operation successful', 'Success');
            return redirect('item-receive-list');
        } catch (\Exception $e) {
           
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function itemReceiveList()
    {
        try {
            $allItemReceiveLists = AramiscItemReceive::with('suppliers','paymentMethodName','bankName')
                ->get();

            return view('backEnd.inventory.itemReceiveList', compact('allItemReceiveLists'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function editItemReceive(Request $request, $id)
    {
        try {
            $expense_head = AramiscChartOfAccount::where('type', 'E')->get();

            $account_id   = AramiscBankAccount::get();

            $editData     = AramiscItemReceive::find($id);

            $editDataChildren = AramiscItemReceiveChild::with('items')->where('item_receive_id', $id)            
                                 ->get();

            $suppliers    = AramiscSupplier::get();

            $itemStores   = AramiscItemStore::get();

            $items        = AramiscItem::get();

            $paymentMethhods = AramiscPaymentMethhod::where('id','!=',3)
                            ->get();

            return view('backEnd.inventory.editItemReceive', compact('editData', 'editDataChildren', 'suppliers', 'itemStores', 'items', 'paymentMethhods', 'expense_head','account_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function updateItemReceiveData(AramiscItemReceiveRequest $request, $id)
    {
       // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        try {
            $total_paid = '';
            if (empty($request->totalPaidValue)) {
                $total_paid = $request->totalPaid;
            } else {
                $total_paid = $request->totalPaidValue;
            }
            $subTotalValue = round($request->subTotalValue);
            $totalDueValue = round($request->totalDueValue);
            $paid_status = '';
            if ($totalDueValue == 0) {
                $paid_status = 'P';
            } elseif ($subTotalValue == $totalDueValue) {
                $paid_status = 'U';
            } else {
                $paid_status = 'PP';
            }
            if(paymentMethodName($request->payment_method)){
                $current_balance_subtraction = AramiscItemReceive::find($id);
                $item_value=$current_balance_subtraction->total_paid;

                $bank_value= AramiscBankAccount::find($request->bank_id);
                $current_bank_value=$bank_value->current_balance;

                $current_balance= AramiscBankAccount::find($request->bank_id);
                $current_balance->current_balance=$current_bank_value+$item_value;
                $current_balance->update();
            }

            $itemReceives = AramiscItemReceive::find($id);
            $itemReceives->supplier_id = $request->supplier_id;
            $itemReceives->store_id = $request->store_id;
            $itemReceives->reference_no = $request->reference_no;
            $itemReceives->receive_date = date('Y-m-d', strtotime($request->receive_date));
            $itemReceives->grand_total = $request->subTotalValue;
            $itemReceives->total_quantity = $request->subTotalQuantityValue;
            $itemReceives->total_paid = $total_paid;
            $itemReceives->paid_status = $paid_status;
            $itemReceives->expense_head_id = $request->expense_head_id;
            $itemReceives->total_due = $request->totalDueValue;
            $itemReceives->payment_method = $request->payment_method;
            $results = $itemReceives->update();

            AramiscAddExpense::where('item_receive_id', $itemReceives->id)->delete();

            $add_expense = new AramiscAddExpense();
            $add_expense->name = 'Item Receive';
            $add_expense->date = date('Y-m-d', strtotime($request->receive_date));
            $add_expense->amount = $total_paid;
            $add_expense->item_receive_id = $itemReceives->id;
            $add_expense->active_status = 1;
            $add_expense->expense_head_id = $request->expense_head_id;
            $add_expense->payment_method_id = $request->payment_method;
            $add_expense->created_by = Auth()->user()->id;
            $add_expense->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $add_expense->un_academic_id = getAcademicId();
            }else{
                $add_expense->academic_id = getAcademicId();
            }
            $add_expense->save();

            if(paymentMethodName($request->payment_method)){
                AramiscBankStatement::where('item_receive_id', $itemReceives->id)
                                    ->where('school_id',Auth::user()->school_id)
                                    ->delete();
                
                
                $bank=AramiscBankAccount::where('id',$request->bank_id)
                    ->where('school_id',Auth::user()->school_id)
                    ->first();
                $after_balance= $bank->current_balance - $total_paid;

                $bank_statement= new AramiscBankStatement();
                $bank_statement->amount= $total_paid;
                $bank_statement->after_balance= $after_balance;
                $bank_statement->type= 0;
                $bank_statement->details= "Item Receive Payment";
                $bank_statement->item_receive_id= $itemReceives->id;
                $bank_statement->payment_date= date('Y-m-d', strtotime($request->receive_date));
                $bank_statement->bank_id= $request->bank_id;
                $bank_statement->school_id= Auth::user()->school_id;
                $bank_statement->payment_method= $request->payment_method;
                $bank_statement->save();


                $current_balance= AramiscBankAccount::find($request->bank_id);
                $current_balance->current_balance=$after_balance;
                $current_balance->update();
            }

            if ($results) {
                $allItemReceiveChildren = AramiscItemReceiveChild::where('item_receive_id', $id)->where('school_id', Auth::user()->school_id)->get();
                foreach ($allItemReceiveChildren as $value) {
                    $items = AramiscItem::find($value->item_id);
                    $items->total_in_stock = $items->total_in_stock - $value->quantity;
                    $results = $items->update();
                }
            }

            $itemReceiveChildren = AramiscItemReceiveChild::where('item_receive_id', $id)->delete();

            if ($itemReceiveChildren) {
                $item_ids = count($request->item_id);
                for ($i = 0; $i < $item_ids; $i++) {
                    if (!empty($request->item_id[$i])) {
                        $itemReceivedChild = new AramiscItemReceiveChild;
                        $itemReceivedChild->item_receive_id = $id;
                        $itemReceivedChild->item_id = $request->item_id[$i];
                        $itemReceivedChild->unit_price = $request->unit_price[$i];
                        $itemReceivedChild->quantity = $request->quantity[$i];
                        $itemReceivedChild->sub_total = $request->totalValue[$i];
                        $itemReceivedChild->created_by = Auth()->user()->id;
                        $itemReceivedChild->school_id = Auth::user()->school_id;
                        if(!moduleStatusCheck('University')){
                            $itemReceivedChild->academic_id = getAcademicId();
                        }
                        $result = $itemReceivedChild->save();

                        if ($result) {
                            $items = AramiscItem::find($request->item_id[$i]);
                            $items->total_in_stock = $items->total_in_stock + $request->quantity[$i];
                            $results = $items->update();
                        }
                    }
                }

                Toastr::success('Operation successful', 'Success');
                return redirect('item-receive-list');
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function viewItemReceive($id)
    {
        try {
            $general_setting = AramiscGeneralSettings::where('school_id', Auth::user()->school_id)->first();
            $viewData = AramiscItemReceive::find($id);
            $editDataChildren = AramiscItemReceiveChild::where('item_receive_id', $id)->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.inventory.viewItemReceive', compact('viewData', 'editDataChildren', 'general_setting'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function itemReceivePayment($id)
    {
        try {
            $paymentDue = AramiscItemReceive::select('total_due')->where('id', $id)->first();

            $editData = AramiscItemReceive::find($id);

            $paymentMethhods = AramiscPaymentMethhod::where('school_id', Auth::user()->school_id)
                ->where('active_status', 1)
                ->get();
            $account_id = AramiscBankAccount::where('school_id', Auth::user()->school_id)
                        ->get();

            $expense_head = AramiscChartOfAccount::where('active_status', '=', 1)
                ->where('school_id', Auth::user()->school_id)
                ->where('type', 'E')
                ->get();

            return view('backEnd.inventory.itemReceivePayment', compact('paymentDue', 'paymentMethhods', 'id', 'expense_head','editData','account_id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function saveItemReceivePayment(Request $request)
    {
      //  DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        try {
            $payments = new AramiscInventoryPayment();
            $payments->item_receive_sell_id = $request->item_receive_id;
            $payments->payment_date = date('Y-m-d', strtotime($request->payment_date));
            $payments->reference_no = $request->reference_no;
            $payments->amount = $request->amount;
            $payments->payment_method = $request->payment_method;
            $payments->notes = $request->notes;
            $payments->payment_type = 'R';
            $payments->created_by = Auth()->user()->id;
            $payments->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $payments->un_academic_id = getAcademicId();
            }else{
                $payments->academic_id = getAcademicId();
            }
            $result = $payments->save();

            $itemPaymentDue = AramiscItemReceive::find($request->item_receive_id);
            if (isset($itemPaymentDue)) {
                $total_due = $itemPaymentDue->total_due;
                $total_paid = $itemPaymentDue->total_paid;
                $updated_total_due = $total_due - $request->amount;
                $updated_total_paid = $total_paid + $request->amount;
                $itemPaymentDue->total_due = $updated_total_due;
                $itemPaymentDue->total_paid = $updated_total_paid;
                if(moduleStatusCheck('University')){
                    $itemPaymentDue->un_academic_id = getAcademicId();
                }else{
                    $itemPaymentDue->academic_id = getAcademicId();
                }
                $result = $itemPaymentDue->update();

                $add_expense = new AramiscAddExpense();
                $add_expense->name = 'Item Receive';
                $add_expense->date = date('Y-m-d', strtotime($request->payment_date));
                $add_expense->amount = $request->amount;
                $add_expense->item_receive_id = $request->item_receive_id;
                $add_expense->active_status = 1;
                $add_expense->expense_head_id = $request->expense_head_id;
                $add_expense->inventory_id = $payments->id;
                $add_expense->payment_method_id = $request->payment_method;
                $add_expense->created_by = Auth()->user()->id;
                $add_expense->school_id = Auth::user()->school_id;
                if(moduleStatusCheck('University')){
                    $add_expense->un_academic_id = getAcademicId();
                }else{
                    $add_expense->academic_id = getAcademicId();
                }
                $add_expense->save();

                if(paymentMethodName($request->payment_method)){
                    $bank=AramiscBankAccount::where('id',$request->bank_id)
                    ->where('school_id',Auth::user()->school_id)
                    ->first();
                    $after_balance= $bank->current_balance - $request->amount;
    
                    $bank_statement= new AramiscBankStatement();
                    $bank_statement->amount= $request->amount;
                    $bank_statement->after_balance= $after_balance;
                    $bank_statement->type= 0;
                    $bank_statement->details= "Item Receive Payment";
                    $bank_statement->item_receive_id= $request->item_receive_id;
                    $bank_statement->item_receive_bank_statement_id = $payments->id;
                    $bank_statement->payment_date= date('Y-m-d', strtotime($request->payment_date));
                    $bank_statement->bank_id= $request->bank_id;
                    $bank_statement->school_id= Auth::user()->school_id;
                    $bank_statement->payment_method= $request->payment_method;
                    $bank_statement->save();

                    $current_balance= AramiscBankAccount::find($request->bank_id);
                    $current_balance->current_balance=$after_balance;
                    $current_balance->update();
                }

            }

            // check if full paid
            $itemReceives = AramiscItemReceive::find($request->item_receive_id);
            if ($itemReceives->total_due == 0) {
                $itemReceives->paid_status = 'P';
            }

            // check if Partial paid
            if ($itemReceives->grand_total > $itemReceives->total_due && $itemReceives->total_due > 0) {
                $itemReceives->paid_status = 'PP';
            }

            $results = $itemReceives->update();

            if ($result) {
                Toastr::success('Operation successful', 'Success');
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

    public function viewReceivePayments($id)
    {

        try {
            $payments = AramiscInventoryPayment::where('item_receive_sell_id', $id)->where('payment_type', 'R')->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.inventory.viewReceivePayments', compact('payments', 'id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteReceivePayment()
    {
        try {
            $receive_payment_id = $_POST['receive_payment_id'];
            $paymentHistory = AramiscInventoryPayment::find($receive_payment_id);
            $item_receive_sell_id = $paymentHistory->item_receive_sell_id;
            $amount = $paymentHistory->amount;

            $itemReceivesData = AramiscItemReceive::find($item_receive_sell_id);
            $itemReceivesData->total_due = $itemReceivesData->total_due + $amount;
            $itemReceivesData->total_paid = $itemReceivesData->total_paid - $amount;

            if(paymentMethodName($itemReceivesData->payment_method)){
                $bank=AramiscBankAccount::where('id',$itemReceivesData->account_id)
                ->where('school_id',Auth::user()->school_id)
                ->first();
                $after_balance= $bank->current_balance + $amount;

                $current_balance= AramiscBankAccount::find($itemReceivesData->account_id);
                $current_balance->current_balance=$after_balance;
                $current_balance->update();

                $delete_balance = AramiscBankStatement::where('item_receive_id',$itemReceivesData->id)
                                ->where('item_receive_bank_statement_id',$paymentHistory->id)
                                ->where('amount',$amount)
                                ->delete();
            }

            $delete_expense=AramiscAddExpense::where('inventory_id',$paymentHistory->id)->delete();

            // check if total due is greater than 0
            if (($itemReceivesData->total_due + $amount) > 0) {
                $itemReceivesData->paid_status = 'PP';
            }
            // check if total due is equal to 0
            if (($itemReceivesData->total_due + $amount) == 0) {
                $itemReceivesData->paid_status = 'P';
            }
            $itemReceivesData->update();
            $result = AramiscInventoryPayment::destroy($receive_payment_id);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteItemReceiveView($id)
    {
        try {
            $title = "Are you sure to detete this Receive item?";
            $url = url('delete-item-receive/' . $id);
            return view('backEnd.modal.delete', compact('id', 'title', 'url'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function deleteItemSaleView($id)
    {
        try {
            $title = "Are you sure to detete this Sale item?";
            $url = url('delete-item-sale/' . $id);
            return view('backEnd.modal.delete', compact('id', 'title', 'url'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteItemReceive($id)
    {
        try {
            $itemReceivedChilds = AramiscItemReceiveChild::where('item_receive_id', $id)
                                ->where('school_id', Auth::user()->school_id)
                                ->get();
            foreach ($itemReceivedChilds as $value) {
                $items = AramiscItem::where('id', $value->item_id)->where('school_id', Auth::user()->school_id)->first();
                $items->total_in_stock = $items->total_in_stock - $value->quantity;
                $results = $items->update();
                $iReceChi = AramiscItemReceiveChild::where('id', $value->id)->where('school_id', Auth::user()->school_id)->delete();
            }
            $result = AramiscItemReceive::where('id', $id)->where('school_id', Auth::user()->school_id)->delete();
            $delete_expense=AramiscAddExpense::where('item_receive_id',$id)->where('school_id', Auth::user()->school_id)->delete();
            
            if ($result) {
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function deleteItemSale($id)
    {
        try {
            $tables = \App\tableList::getTableList('item_sell_id', $id);
            try {
                $result = AramiscItemSell::destroy($id);
                if ($result) {
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

    public function cancelItemReceiveView($id)
    {

        try {
            return view('backEnd.inventory.cancelItemReceiveView', compact('id'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function cancelItemReceive($id)
    {
        try {
            $itemReceives = AramiscItemReceive::find($id);
            $itemReceives->paid_status = 'R';
            $results = $itemReceives->update();

            $itemReceives->expnese_head_id;
            $refund = AramiscAddExpense::where('item_receive_id',$itemReceives->id)
                        ->where('school_id', Auth::user()->school_id)
                        ->delete();

            if(paymentMethodName($itemReceives->payment_method)){
                $reset_balance = AramiscBankStatement::where('item_receive_id',$itemReceives->id)
                                ->where('school_id',Auth::user()->school_id)
                                ->sum('amount');

                    $bank=AramiscBankAccount::where('id',$itemReceives->account_id)
                    ->where('school_id',Auth::user()->school_id)
                    ->first();
                    $after_balance= $bank->current_balance + $reset_balance;

                    $current_balance= AramiscBankAccount::find($itemReceives->account_id);
                    $current_balance->current_balance=$after_balance;
                    $current_balance->update();

                    $delete_balance = AramiscBankStatement::where('item_receive_id',$itemReceives->id)
                                        ->where('school_id',Auth::user()->school_id)
                                        ->delete();
            }
            if ($results) {
                $itemReceiveChild = AramiscItemReceiveChild::where('item_receive_id', $id)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                if (!empty($itemReceiveChild)) {
                    foreach ($itemReceiveChild as $value) {
                        $items = AramiscItem::find($value->item_id);
                        $items->total_in_stock = $items->total_in_stock - $value->quantity;
                        $result = $items->update();
                    }
                }
                Toastr::success('Operation successful', 'Success');
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
    
    # This is for upadate database aramisc_item_receive_children table for the issue of  float/double datatype only stores 8 digits.

    public static function updateAramiscItemReceiveDatabase()
    {
        try {
            Schema::table('aramisc_item_receives', function (Blueprint $table) {
                $table->decimal('grand_total', 20, 2)->change();
                $table->decimal('total_quantity', 20, 2)->change();
                $table->decimal('total_paid', 20, 2)->change();
                $table->decimal('total_due', 20, 2)->change();
            });
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public static function updateAramiscItemReceiveChildrenDatabase()
    {
        try {
            Schema::table('aramisc_item_receive_children', function (Blueprint $table) {
                $table->decimal('unit_price', 20, 2)->change();
                $table->decimal('quantity', 20, 2)->change();
                $table->decimal('sub_total', 20, 2)->change();
            });
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
