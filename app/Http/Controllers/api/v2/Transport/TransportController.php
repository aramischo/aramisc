<?php

namespace App\Http\Controllers\api\v2\Transport;

use App\AramiscStudent;
use App\AramiscAssignVehicle;
use App\Scopes\SchoolScope;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\v2\StudentTransportResource;

class TransportController extends Controller
{
    public function studentTransport(Request $request)
    {

        $user = AramiscStudent::withoutGlobalScope(SchoolScope::class)
            ->with(['user' => function ($q) {
                $q->where('school_id', auth()->user()->school_id);
            }])
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($request->student_id);

        $routes = AramiscAssignVehicle::with('route', 'vehicle')
            ->join('aramisc_vehicles', 'aramisc_assign_vehicles.vehicle_id', 'aramisc_vehicles.id')
            ->join('aramisc_students', 'aramisc_vehicles.id', 'aramisc_students.vechile_id')
            ->where('aramisc_assign_vehicles.active_status', 1)
            ->where('aramisc_students.user_id', $user->user->id)
            ->where('aramisc_assign_vehicles.school_id', auth()->user()->school_id)
            ->get();

        $data = StudentTransportResource::collection($routes);

        if (!$data) {
            $response = [
                'success' => false,
                'data'    => null,
                'message' => 'Operation failed'
            ];
        } else {
            $response = [
                'success' => true,
                'data'    => $data,
                'message' => 'Transport list'
            ];
        }
        return response()->json($response);
    }
}
