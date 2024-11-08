<?php

namespace App\Http\Controllers;

use App\AramiscRoute;
use App\YearCheck;
use App\ApiBaseMethod;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AramiscRouteController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
	}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        try {
            $routes = AramiscRoute::where('school_id', Auth::user()->school_id)->orderby('id','DESC')->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($routes, null);
            }
            return view('backEnd.transport.route', compact('routes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required|max:200',
            'far' => "required"
        ]);

        // school wise uquine validation
        $is_duplicate = AramiscRoute::where('school_id', Auth::user()->school_id)->where('title', $request->title)->first();
        if ($is_duplicate) {
            Toastr::error('Duplicate name found!', 'Failed');
            return redirect()->back()->withErrors($validator)->withInput();
        }


        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        try {
            $route = new AramiscRoute();
            $route->title = $request->title;
            $route->far = $request->far;
            $route->school_id = Auth::user()->school_id;
            $route->academic_id = getAcademicId();
            $result = $route->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Route has been created successfully');
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
        } catch (\Exception $e) {
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

        try {
            // $route = AramiscRoute::find($id);
             if (checkAdmin()) {
                $route = AramiscRoute::find($id);
            }else{
                $route = AramiscRoute::where('id',$id)->where('school_id',Auth::user()->school_id)->first();
            }
            $routes = AramiscRoute::where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['route'] = $route->toArray();
                $data['routes'] = $routes->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.transport.route', compact('route', 'routes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function update(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required|max:200',
            'far' => "required"
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // school wise uquine validation
        $is_duplicate = AramiscRoute::where('school_id', Auth::user()->school_id)->where('title', $request->title)->where('id', '!=', $request->id)->first();
        if ($is_duplicate) {
            Toastr::error('Duplicate name found!', 'Failed');
            return redirect()->back()->withErrors($validator)->withInput();
        }


        try {
            // $route = AramiscRoute::find($request->id);
            if (checkAdmin()) {
                $route = AramiscRoute::find($request->id);
            }else{
                $route = AramiscRoute::where('id',$request->id)->where('school_id',Auth::user()->school_id)->first();
            }
            $route->title = $request->title;
            $route->far = $request->far;
            $result = $route->save();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Route has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('transport-route');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
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

        try {
            $tables = \App\tableList::getTableList('route_id', $id);
            try {
                if ($tables == null) {
                    // $route = AramiscRoute::destroy($id);
                     if (checkAdmin()) {
                            $route = AramiscRoute::destroy($id);
                        }else{
                            $route = AramiscRoute::where('id',$id)->where('school_id',Auth::user()->school_id)->delete();
                        }
                    if ($route) {
                        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                            if($route){
                                return ApiBaseMethod::sendResponse(null, 'Route has been deleted successfully');
                            }else{
                                return ApiBaseMethod::sendError('Something went wrong, please try again.');
                            }
                        } else {
                            if ($route) {
                                Toastr::success('Operation successful', 'Success');
                                return redirect()->back();
                            } else {
                                Toastr::error('Operation Failed', 'Failed');
                                return redirect()->back();
                            }
                        }
                    } else {
                        Toastr::error('Operation Failed', 'Failed');
                        return redirect()->back();
                    }
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