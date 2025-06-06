<?php

namespace App\Http\Controllers\api\v2\Admin;

use App\AramiscRoute;
use App\AramiscAcademicYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    public function routeList()
    {
        $routes = AramiscRoute::where('school_id', auth()->user()->school_id)->select('id', 'title', 'far')->get();
        if (!$routes) {
            $response = [
                'success' => false,
                'data'    => null,
                'message' => 'Operation failed'
            ];
        } else {
            $response = [
                'success' => true,
                'data'    => $routes,
                'message' => 'Route list successful'
            ];
        }
        return response()->json($response);
    }

    public function storeRoute(Request $request)
    {
        $this->validate($request, [
            'title' => ['required', 'max:200', Rule::unique('aramisc_routes', 'title')->where('school_id', auth()->user()->school_id)],
            'far'   => 'required|numeric'
        ]);

        $route = new AramiscRoute();
        $route->title = $request->title;
        $route->far = $request->far;
        $route->school_id = Auth::user()->school_id;
        $route->academic_id = AramiscAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
        $route->save();

        $data = AramiscRoute::select('id', 'title', 'far')->find($route->id);

        if (!$data) {
            $response = [
                'success' => false,
                'data'    => null,
                'message' => 'Operation failed'
            ];
        } else {
            $response = [
                'success' => true,
                'data'    => [$data],
                'message' => 'The route created successfully'
            ];
        }
        return response()->json($response);
    }

    public function updateRoute(Request $request)
    {
        $school_id = auth()->user()->school_id;
        $this->validate($request, [
            'route_id' => ['required', Rule::exists('aramisc_routes', 'id')->where('school_id', $school_id)],
            'title' => ['required', 'max:200', Rule::unique('aramisc_routes', 'title')->where('school_id', $school_id)->ignore($request->route_id)],
            'far' => "required|numeric"
        ], [
            'route_id.exists' => 'Invalid route'
        ]);

        $route          = AramiscRoute::where('school_id', auth()->user()->school_id)->where('id', $request->route_id)->first();
        $route->title   = $request->title;
        $route->far     = $request->far;
        $route->save();
        $data = AramiscRoute::select('id', 'title', 'far')->find($route->id);
        if (!$data) {
            $response = [
                'success' => false,
                'data'    => null,
                'message' => 'Operation failed'
            ];
        } else {
            $response = [
                'success' => true,
                'data'    => [$data],
                'message' => 'The route updated successfully'
            ];
        }
        return response()->json($response);
    }

    public function deleteRoute(Request $request)
    {

        $this->validate($request, [
            'route_id' => ['required', Rule::exists('aramisc_routes', 'id')->where('school_id', auth()->user()->school_id)]
        ], [
            'route_id.exists' => 'Invalid route'
        ]);

        $id = $request->route_id;

        $tables = \App\tableList::getTableList('route_id', $id);

        if ($tables == null) {
            $delete = AramiscRoute::where('school_id', auth()->user()->school_id)->where('id', $id)->delete();
        } else {
            $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
        }

        if (!$delete) {
            $response = [
                'success' => false,
                'data'    => null,
                'message' => $msg
            ];
        } else {
            $response = [
                'success' => true,
                'data'    => null,
                'message' => 'The route deleted successfully'
            ];
        }
        return response()->json($response);
    }
}
