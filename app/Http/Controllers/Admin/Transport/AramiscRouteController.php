<?php

namespace App\Http\Controllers\Admin\Transport;

use App\AramiscRoute;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Transport\AramiscRouteRequest;

class AramiscRouteController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
	}

    public function index(Request $request)
    {
        try {
            $routes = AramiscRoute::get();
            return view('backEnd.transport.route', compact('routes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function store(AramiscRouteRequest $request)
    {
        try {
            $route = new AramiscRoute();
            $route->title = $request->title;
            $route->far = $request->far;
            $route->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $route->un_academic_id = getAcademicId();
            }else{
                $route->academic_id = getAcademicId();
            }
            $route->save();

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
            $route = AramiscRoute::find($id);
            $routes = AramiscRoute::where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.transport.route', compact('route', 'routes'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(AramiscRouteRequest $request, $id)
    {
        try {
            $route = AramiscRoute::find($request->id);            
            $route->title = $request->title;
            $route->far = $request->far;
            if(moduleStatusCheck('University')){
                $route->un_academic_id = getAcademicId();
            }
            $route->save();

            Toastr::success('Operation successful', 'Success');
            return redirect('transport-route');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $tables = \App\tableList::getTableList('route_id', $id);
            try {
                if ($tables == null) {
                    AramiscRoute::destroy($id);

                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
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