<?php

namespace App\Http\Controllers\Admin\Transport;

use App\AramiscClass;
use App\AramiscRoute;
use App\AramiscStudent;
use App\AramiscVehicle;
use App\YearCheck;
use App\ApiBaseMethod;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Admin\StudentInfo\AramiscStudentReportController;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmTransportController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
        // User::checkAuth();
    }

    public function studentTransportReport(Request $request)
    {
        try {
            $classes = AramiscClass::get();
            $routes = AramiscRoute::get();
            $vehicles = AramiscVehicle::status()->get();

            return view('backEnd.transport.student_transport_report', compact('classes', 'routes', 'vehicles'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function studentTransportReportSearch(Request $request)
    {
        $input = $request->all();
        if (moduleStatusCheck('University')) {
            $validator = Validator::make($input, [
                'un_session_id' => "required",
                'route' => "required",
                'vehicle' => "required",
            ]);
        } else {
            $validator = Validator::make($input, [
                'class' => "required",
                'section' => "required",
                'route' => "required",
                'vehicle' => "required",
            ]);
        }


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {
            $student_ids = [];
            $data = [];
            $student_records = StudentRecord::query();
            $classes = AramiscClass::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            if (moduleStatusCheck('University')) {
                $student_ids = universityFilter($student_records, $request)
                    ->distinct('student_id')->get('student_id');
                $stdent_ids = [];
                foreach ($student_ids as $record) {
                    $stdent_ids[] = $record->student_id;
                }
            } else {
                $student_ids = AramiscStudentReportController::classSectionStudent($request);
            }

            $students = AramiscStudent::where('active_status', 1)
                ->whereHas('studentRecord', function ($query) use ($request) {
                    $query->when($request->class, function ($q) use ($request) {
                        $q->where('class_id', $request->class);
                    });
                    $query->when($request->section, function ($q) use ($request) {
                        $q->where('section_id', $request->section);
                    });
                })
                ->when($request->route, function ($q) use ($request) {
                    $q->where('route_list_id', $request->route);
                })
                ->when($request->vehicle, function ($q) use ($request) {
                    $q->where('vechile_id', $request->vehicle);
                })
                ->whereIn('id', $student_ids)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $routes = AramiscRoute::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $vehicles = AramiscVehicle::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();

            $data['classes'] = $classes;
            $data['routes'] = $routes;
            $data['vehicles'] = $vehicles;
            $data['students'] = $students;
            $data['class_id'] = $request->class;
            $data['section_id'] = $request->section_id;
            $data['route_id'] = $request->route;
            $data['vechile_id'] =  $request->vehicle;
            if (moduleStatusCheck('University')) {
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data += $interface->getCommonData($request);
            }
            return view('backEnd.transport.student_transport_report', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function studentTransportReportApi(Request $request)
    {

        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $transport = DB::table('aramisc_assign_vehicles')
                    ->select('aramisc_routes.title as route', 'aramisc_vehicles.vehicle_no', 'aramisc_vehicles.vehicle_model', 'aramisc_vehicles.made_year', 'aramisc_staffs.full_name as driver_name', 'aramisc_staffs.mobile', 'aramisc_staffs.driving_license')
                    ->join('aramisc_routes', 'aramisc_assign_vehicles.route_id', '=', 'aramisc_routes.id')
                    ->join('aramisc_vehicles', 'aramisc_assign_vehicles.vehicle_id', '=', 'aramisc_vehicles.id')
                    ->join('aramisc_staffs', 'aramisc_vehicles.driver_id', '=', 'aramisc_staffs.id')
                    ->where('school_id', Auth::user()->school_id)->get();

                return ApiBaseMethod::sendResponse($transport, null);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
