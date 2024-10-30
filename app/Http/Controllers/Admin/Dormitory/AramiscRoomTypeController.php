<?php

namespace App\Http\Controllers\Admin\Dormitory;


use App\AramiscRoomType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class AramiscRoomTypeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $room_types = AramiscRoomType::get();         
            return view('backEnd.dormitory.room_type', compact('room_types'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input,[
            'type' => "required",
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        

        try {
            $room_type = new AramiscRoomType();
            $room_type->type = $request->type;
            $room_type->description = $request->description;
            $room_type->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $room_type->un_academic_id = getAcademicId();
            }else{
                $room_type->academic_id = getAcademicId();
            }
            $room_type->save();

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
            $room_type = AramiscRoomType::find($id);
            $room_types = AramiscRoomType::where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.dormitory.room_type', compact('room_types', 'room_type'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $room_type = AramiscRoomType::find($request->id);
            $room_type->type = $request->type;
            $room_type->description = $request->description;
            if(moduleStatusCheck('University')){
                $room_type->un_academic_id = getAcademicId();
            }
            $room_type->save();

            Toastr::success('Operation successful', 'Success');
            return redirect('room-type');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $tables = \App\tableList::getTableList('room_type_id', $id);
            try {
                if ($tables == null) {
                    AramiscRoomType::destroy($id);

                    Toastr::success('Operation successful', 'Success');
                    return redirect('room-type');
                } else {
                    $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';

                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                
                Toastr::error($msg, 'Failed');
                return redirect()->back();
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}