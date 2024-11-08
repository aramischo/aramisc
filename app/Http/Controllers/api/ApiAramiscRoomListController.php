<?php

namespace App\Http\Controllers\api;

use Validator;
use App\tableList;
use App\AramiscRoomList;
use App\AramiscRoomType;
use App\ApiBaseMethod;
use App\AramiscDormitoryList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;

class ApiAramiscRoomListController extends Controller
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
            $room_lists = AramiscRoomList::where('active_status', 1)->get();
            $room_types = AramiscRoomType::where('active_status', 1)->get();
            $dormitory_lists = AramiscDormitoryList::where('active_status', 1)->get();
    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['room_lists'] = $room_lists->toArray();
                $data['room_types'] = $room_types->toArray();
                $data['dormitory_lists'] = $dormitory_lists->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.dormitory.room_list', compact('room_lists', 'room_types', 'dormitory_lists'));
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
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => "required|max:100",
            'dormitory' => "required",
            'room_type' => "required",
            'number_of_bed' => "required|max:2",
            'cost_per_bed' => "required|max:11"
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
            $room_list = new AramiscRoomList();
            $room_list->name = $request->name;
            $room_list->dormitory_id = $request->dormitory;
            $room_list->room_type_id = $request->room_type;
            $room_list->number_of_bed = $request->number_of_bed;
            $room_list->cost_per_bed = $request->cost_per_bed;
            $room_list->description = $request->description;
            $result = $room_list->save();
    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Room has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
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
    public function show(Request $request, $id)
    {
        
        try{
            $room_list = AramiscRoomList::find($id);
            $room_lists = AramiscRoomList::all();
            $room_types = AramiscRoomType::all();
            $dormitory_lists = AramiscDormitoryList::all();
    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['room_list'] = $room_list->toArray();
                $data['room_lists'] = $room_lists->toArray();
                $data['room_types'] = $room_types->toArray();
                $data['dormitory_lists'] = $dormitory_lists->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.dormitory.room_list', compact('room_lists', 'room_list', 'room_types', 'dormitory_lists'));
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back(); 
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
            'name' => 'required|max:100',
            'dormitory' => "required",
            'room_type' => "required",
            'number_of_bed' => "required|max:2",
            'cost_per_bed' => "required|max:11"
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
            $room_list = AramiscRoomList::find($request->id);
            $room_list->name = $request->name;
            $room_list->dormitory_id = $request->dormitory;
            $room_list->room_type_id = $request->room_type;
            $room_list->number_of_bed = $request->number_of_bed;
            $room_list->cost_per_bed = $request->cost_per_bed;
            $room_list->description = $request->description;
            $result = $room_list->save();
    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Room has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('room-list');
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
    public function destroy(Request $request, $id)
    {        
        try{
            $key_id = 'room_id';

            $tables = tableList::getTableList($key_id,$id);
            try {
                $delete_query = AramiscRoomList::destroy($id);
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Room has been deleted successfully');
                    } else {
                        return ApiBaseMethod::sendError('Something went wrong, please try again.');
                    }
                } else {
                    if ($delete_query) {
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error('This item already used', 'Failed');
                return redirect()->back();
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back(); 
        }
    }
}
