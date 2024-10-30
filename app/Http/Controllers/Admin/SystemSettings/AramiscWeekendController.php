<?php

namespace App\Http\Controllers\Admin\SystemSettings;
use App\AramiscWeekend;
use App\YearCheck;
use App\ApiBaseMethod;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AramiscWeekendController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
	}

    public function index(Request $request)
    {
        try{
            // $weekends = AramiscWeekend::where('school_id', Auth::user()->school_id)->get();
            $weekends = AramiscWeekend::where('school_id', Auth::user()->school_id)->get();
            
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($weekends, null);
            }
            
            return view('backEnd.systemSettings.weekend', compact('weekends'));
        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }

    public function store(Request $request)
    {
        try{

            $day_id = $request->day_id;
            $status = $request->status;

            $weekend = AramiscWeekend::find($day_id);
            if($weekend){
                $weekend->is_weekend = $status;
                $weekend->save();
            }

            return response(["done"]);
        }


        catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }



        // $weekends_count = AramiscWeekend::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->count();
        //     if ($weekends_count==7) {
        //         Toastr::warning('You have already added 7 days, Now You can edit', 'Not Added');
        //         return redirect()->back();
        //     }
        // try{
		// 	$weekend = new AramiscWeekend();
        //     $weekend->name = $request->name;
        //     if ($request->name=='saturday') {
        //         $weekend->order =1;
        //     }
        //     if ($request->name=='sunday') {
        //         $weekend->order =1;
        //     }
        //     if ($request->name=='monday') {
        //         $weekend->order =1;
        //     }
        //     if ($request->name=='tuesday') {
        //         $weekend->order =1;
        //     }
        //     if ($request->name=='wednesday') {
        //         $weekend->order =1;
        //     }
        //     if ($request->name=='thursday') {
        //         $weekend->order =1;
        //     }
        //     if ($request->name=='friday') {
        //         $weekend->order =1;
        //     }
        //     if (isset($request->make_weekend)) {
        //         $weekend->is_weekend = 1;
        //     } else {
        //         $weekend->is_weekend = 0;
        //     }
        //     $weekend->academic_id = getAcademicId();
        //     $result = $weekend->save();

        //     if (ApiBaseMethod::checkUrl($request->fullUrl())) {
        //         if ($result) {
        //             return ApiBaseMethod::sendResponse(null, 'Weekend has been added successfully.');
        //         } else {
        //             return ApiBaseMethod::sendError('Something went wrong, please try again.');
        //         }
        //     } else {
        //         if ($result) {
        //             Toastr::success('Operation successful', 'Success');
        //             return redirect()->back();
        //         } else {
        //             Toastr::error('Operation Failed', 'Failed');
        //             return redirect()->back();
        //         }
        //     }
        // }catch (\Exception $e) {
        //     Toastr::error('Operation Failed', 'Failed');
        //     return redirect()->back();
        // }
    }

    public function edit(Request $request, $id)
    {
        try{
            // $editData = AramiscWeekend::find($id);
            if (checkAdmin()) {
                    $editData = AramiscWeekend::find($id);
                }else{
                    $editData = AramiscWeekend::where('id',$id)->where('school_id',Auth::user()->school_id)->first();
                }
            $weekends = AramiscWeekend::where('school_id', Auth::user()->school_id)->get();
    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['editData'] = $editData->toArray();
                $data['weekends'] = $weekends->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.systemSettings.weekend', compact('weekends', 'editData'));
        }catch (\Exception $e) {
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try{
            // $weekend = AramiscWeekend::find($request->id);
            if (checkAdmin()) {
                $weekend = AramiscWeekend::find($request->id);
            }else{
                $weekend = AramiscWeekend::where('id',$request->id)->where('school_id',Auth::user()->school_id)->first();
            }
            $weekend->name = $request->name;

            if (isset($request->make_weekend)) {
                $weekend->is_weekend = 1;
            } else {
                $weekend->is_weekend = 0;
            }

            $result = $weekend->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Weekend has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('weekend');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        }catch (\Exception $e) {
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
        }
    }


}