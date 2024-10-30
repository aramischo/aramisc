<?php

namespace App\Http\Controllers\Admin\GeneralSettings;


use App\AramiscCurrency;
use App\AramiscGeneralSettings;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\GeneralSettings\AramiscCurrencyRequest;

class SmManageCurrencyController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');

    }
  
    // manage currency
      public function manageCurrency()
      {
          try {            
              $currencies = AramiscCurrency::with('active')->whereIn('school_id', [1, Auth::user()->school_id])->get();
              return view('backEnd.systemSettings.manageCurrency', compact('currencies'));
          } catch (\Exception $e) {
              Toastr::error('Operation Failed', 'Failed');
              return redirect()->back();
          }
      }
      public function create()
      {
        return view('backEnd.systemSettings.create_update_currency');
      }
      public function storeCurrency(AramiscCurrencyRequest $request)
      {
          try {
              $s = new AramiscCurrency();
              $s->name = $request->name;
              $s->code = $request->code;
              $s->symbol = $request->symbol;
              $s->currency_type = $request->currency_type;
              $s->currency_position = $request->currency_position;
              $s->space = $request->space;
              $s->decimal_digit = $request->decimal_digit;
              $s->decimal_separator = $request->decimal_separator;
              $s->thousand_separator = $request->thousand_separator;
              $s->school_id = Auth::user()->school_id;
              $s->save();
              Toastr::success('Operation successful', 'Success');
              return redirect('manage-currency');
          } catch (\Exception $e) {
              return $e->getMessage();
          }
      }
  
      public function storeCurrencyUpdate(AramiscCurrencyRequest $request)
      {
          try {
              $s = AramiscCurrency::findOrFail($request->id);
              $s->name = $request->name;
              $s->code = $request->code;
              $s->symbol = $request->symbol;
              $s->currency_type = $request->currency_type;
              $s->currency_position = $request->currency_position;
              $s->space = $request->space;
              $s->decimal_digit = $request->decimal_digit;
              $s->decimal_separator = $request->decimal_separator;
              $s->thousand_separator = $request->thousand_separator;
              $s->school_id = Auth::user()->school_id;
              $s->update();
  
              Toastr::success('Operation successful', 'Success');
              return redirect('manage-currency');

          } catch (\Exception $e) {
              Toastr::error('Operation Failed', 'Failed');
              return redirect('manage-currency');
          }
      }
  
      public function manageCurrencyEdit($id)
      {
        // if (config('app.app_sync') == true) {
        //     Toastr::error('Disabled for demo mode', 'Failed');
        //     return redirect()->route('manage-currency');
        // }
        try {
            $currencies = AramiscCurrency::whereOr(['school_id', Auth::user()->school_id], ['school_id', 1])->get();
            $editData = AramiscCurrency::where('id', $id)->first();

            return view('backEnd.systemSettings.create_update_currency', compact('editData', 'currencies'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect('manage-currency');
        }
      }
  
      public function manageCurrencyDelete($id)
      {
        // if (config('app.app_sync') == true) {
        //     Toastr::error('Disabled for demo mode', 'Failed');
        //     return redirect()->route('manage-currency');
        // }
        try {
            $current_currency = AramiscGeneralSettings::where('school_id', Auth::user()->school_id)->where('currency', @schoolConfig()->currency)->where('currency_symbol', @schoolConfig()->currency_symbol)->first();
            $del_currency = AramiscCurrency::findOrfail($id);

            if (!empty($current_currency) && $current_currency->currency == $del_currency->code && $current_currency->currency_symbol == $del_currency->symbol) {
                Toastr::warning('You cannot delete current currency', 'Warning');
                return redirect()->back();
            } else {
                $currency = AramiscCurrency::findOrfail($id);
                $currency->delete();
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            }
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
      }
      public function manageCurrencyActive(int $id)
      {
        if (config('app.app_sync') == true) {
            Toastr::error('Disabled for demo mode', 'Failed');
            return redirect()->route('manage-currency');
        }
        try {
            $currency = AramiscCurrency::findOrFail($id);

            $systemSettings = generalSetting();
            $systemSettings->currency = $currency->code;
            $systemSettings->currency_symbol = $currency->symbol;
            $systemSettings->save();

            if ($systemSettings) {
                session()->forget('generalSetting');
                session()->put('generalSetting', $systemSettings);
            }
            Toastr::success('Operation successful', 'Success');
            return redirect('manage-currency');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect('manage-currency');
        }
      }
      public function systemDestroyedByAuthorized()
      {
          try {
              return view('backEnd.systemSettings.manageCurrency', compact('editData', 'currencies'));
          } catch (\Exception $e) {
  
              Toastr::error('Operation Failed', 'Failed');
              return redirect()->back();
          }
      }
}
