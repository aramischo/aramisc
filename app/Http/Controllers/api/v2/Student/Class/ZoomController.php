<?php

namespace App\Http\Controllers\api\v2\Student\Class;

use App\User;
use Carbon\Carbon;
use App\ApiBaseMethod;
use App\SmClassSection;
use App\SmGeneralSettings;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\DB;
use MacsiDigital\Zoom\Facades\Zoom;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Zoom\Entities\ZoomMeeting;
use Modules\Zoom\Entities\ZoomSetting;
use Modules\Zoom\Entities\VirtualClass;
use Modules\Lms\Entities\LessonComplete;
use Modules\RolePermission\Entities\InfixRole;
use Modules\Zoom\Http\Requests\VirtualClassRequest;
use App\Http\Resources\v2\Class\Student\Zoom\ClassResource;
use App\Http\Resources\v2\Class\Student\Zoom\MeetingResource;
use App\Http\Resources\v2\Class\Student\Zoom\TeacherResource;
use App\Models\User as ModelsUser;
use App\SmParent;
use Modules\Zoom\Repositories\Interfaces\VirtualClassRepositoryInterface;

class ZoomController extends Controller
{
    
}