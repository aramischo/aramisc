<?php

namespace App\Http\Controllers\api\v2\Student\Class;

use App\User;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Gmeet\Entities\GmeetVirtualClass;
use App\Http\Resources\v2\Class\Student\GMeet\MeetingResource;
use Modules\Gmeet\Entities\GmeetVirtualMeeting;
use Modules\Gmeet\Repositories\Interfaces\GmeetVirtualClassRepositoryInterface;

class GMeetController extends Controller
{

}
