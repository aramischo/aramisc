<?php

namespace App\Http\Controllers\Customer;

use App\AramiscStaff;
use App\AramiscProductPurchase;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

class AramiscCustomerPanelController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
    }

    public function customerDashboard()
    {
        $id = Auth::user()->id;
        $staffDetails = AramiscStaff::where('user_id', $id)->get();
        try {
            return view('backEnd.customerPanel.customer_dashboard', compact('staffDetails'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function customerPurchases()
    {
        try {
            $id = Auth::user()->id;
            $customerDetails = AramiscStaff::where('user_id', $id)->get();
            $ProductPurchase = AramiscProductPurchase::where('user_id', $id)->get();
            return view('backEnd.customerPanel.customer_purchase', compact('customerDetails', 'ProductPurchase'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
