<?php

namespace App\Http\Controllers\Admin\AdminSection;


use App\AramiscComplaint;
use App\AramiscSetupAdmin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\AdminSection\AramiscComplaintRequest;


class AramiscComplaintController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
	}

    public function index(Request $request)
    {
        try {
            $complaints = AramiscComplaint::with('complaintType','complaintSource')->get();
            $complaint_types = AramiscSetupAdmin::where('type', 2)->get();
            $complaint_sources = AramiscSetupAdmin::where('type', 3)->get();
            return view('backEnd.admin.complaint', compact('complaints', 'complaint_types', 'complaint_sources'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function create()
    {
        //
    }

    public function store(AramiscComplaintRequest $request)
    {
        try {
            $destination =  'public/uploads/complaint/';
            $fileName=fileUpload($request->file,$destination);
            $complaint = new AramiscComplaint();
            $complaint->complaint_by = $request->complaint_by;
            $complaint->complaint_type = $request->complaint_type;
            $complaint->complaint_source = $request->complaint_source;
            $complaint->phone = $request->phone;
            $complaint->date = date('Y-m-d', strtotime($request->date));
            $complaint->description = $request->description;
            $complaint->action_taken = $request->action_taken;
            $complaint->assigned = $request->assigned;
            $complaint->file = $fileName;
            $complaint->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $complaint->un_academic_id = getAcademicId();
            }else{
                $complaint->academic_id = getAcademicId();
            }
            $complaint->save();

            Toastr::success('Operation successful', 'Success');
            return redirect('complaint');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }



    public function show($id)
    {

        try {
            $complaint = AramiscComplaint::find($id);
            return view('backEnd.admin.complaintDetails', compact('complaint'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function edit(Request $request, $id)
    {
        try {
            $complaints = AramiscComplaint::get();
            $complaint = AramiscComplaint::find($id);
            $complaint_types = AramiscSetupAdmin::where('type', 2)->get();
            $complaint_sources = AramiscSetupAdmin::where('type', 3)->get();
            return view('backEnd.admin.complaint', compact('complaint', 'complaints', 'complaint_types', 'complaint_sources'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }


    public function update(AramiscComplaintRequest $request)
    {
        try {
            $destination =  'public/uploads/complaint/';
            $complaint = AramiscComplaint::find($request->id);
            $complaint->complaint_by = $request->complaint_by;
            $complaint->complaint_type = $request->complaint_type;
            $complaint->complaint_source = $request->complaint_source;
            $complaint->phone = $request->phone;
            $complaint->date = date('Y-m-d', strtotime($request->date));
            $complaint->description = $request->description;
            $complaint->action_taken = $request->action_taken;
            $complaint->assigned = $request->assigned;        
            $complaint->file = fileUpdate($complaint->file,$request->file,$destination);          
            $complaint->save();

            Toastr::success('Operation successful', 'Success');
            return redirect('complaint');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function destroy(Request $request)
    {
        try {
            $complaint = AramiscComplaint::find($request->id);
            if ($complaint->file != "") {
                if (file_exists($complaint->file)) {
                    unlink($complaint->file);
                }
            }
            $complaint->delete();

            Toastr::success('Operation successful', 'Success');
            return redirect('complaint');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function complaint()
    {
        $complaints = AramiscComplaint::all();
        return $this->sendResponse($complaints->toArray(), 'Complaint retrieved successfully.');
    }
}