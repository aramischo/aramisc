<?php

namespace App\Http\Controllers\api;

use Validator;
use App\AramiscWeekend;
use App\YearCheck;
use App\ApiBaseMethod;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;

class ApiAramiscWeekendController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try{
            $weekends = AramiscWeekend::where('school_id', 1)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($weekends, null);
            }
            return view('backEnd.systemSettings.weekend', compact('weekends'));
        }catch (\Exception $e) {
       
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
        }
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            $weekend = AramiscWeekend::find(1);
            $weekend->saturday = isset($request->saturday) ? 1 : 0;
            $weekend->sunday = isset($request->sunday) ? 2 : 0;
            $weekend->monday = isset($request->monday) ? 3 : 0;
            $weekend->tuesday = isset($request->tuesday) ? 4 : 0;
            $weekend->wednesday = isset($request->wednesday) ? 5 : 0;
            $weekend->thursday = isset($request->thursday) ? 6 : 0;
            $weekend->friday = isset($request->friday) ? 7 : 0;
            $result = $weekend->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Weekend has been added successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        try{
            $editData = AramiscWeekend::find($id);
            $weekends = AramiscWeekend::all();
    
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
            $weekend = AramiscWeekend::find($request->id);
            $weekend->name = $request->name;
    
            if (isset($request->weekend)) {
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
