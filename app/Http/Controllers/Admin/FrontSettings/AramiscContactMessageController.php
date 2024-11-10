<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\AramiscContactPage;
use App\AramiscContactMessage;
use App\AramiscGeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

class AramiscContactMessageController extends Controller
{

    public function __construct()
	{
        $this->middleware('PM');
       
    }
    public function deleteMessage($id)
    {
        try {
            AramiscContactMessage::find($id)->delete();
            Toastr::success('Operation successful', 'Success');
            return redirect('contact-message');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
