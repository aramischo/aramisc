<?php

namespace App\Http\Controllers\Admin\SystemSettings;


use App\tableList;
use App\AramiscBaseGroup;
use App\AramiscBaseSetup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\GeneralSettings\AramiscBaseSetupRequest;


class AramiscBaseSetupController extends Controller
{
	public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
	}

	public function index()
	{

		try {
			$base_groups = AramiscBaseGroup::where('active_status', '=', 1)->get();
			return view('backEnd.systemSettings.baseSetup.base_setup', compact('base_groups'));
		} catch (\Exception $e) {
			Toastr::error('Operation Failed', 'Failed');
			return redirect()->back();
		}
	}
	public function store(AramiscBaseSetupRequest $request)
	{


		try {

			$base_setup = new AramiscBaseSetup();
			$base_setup->base_setup_name = $request->name;
			$base_setup->base_group_id = $request->base_group;
			$base_setup->school_id = Auth::user()->school_id;
			$base_setup->save();

			Toastr::success('Operation successful', 'Success');
			return redirect()->back();

		} catch (\Exception $e) {
			Toastr::error('Operation Failed', 'Failed');
			return redirect()->back();
		}
	}
	public function edit($id)
	{

		try {
			 $base_setup = AramiscBaseSetup::find($id);			 
			$base_groups = AramiscBaseGroup::where('active_status', '=', 1)->get();

			return view('backEnd.systemSettings.baseSetup.base_setup', compact('base_setup', 'base_groups'));
		} catch (\Exception $e) {
			Toastr::error('Operation Failed', 'Failed');
			return redirect()->back();
		}
	}

	public function update(AramiscBaseSetupRequest $request)
	{

		try {
			$base_setup = AramiscBaseSetup::find($request->id);

			$base_setup->base_setup_name = $request->name;
			$base_setup->base_group_id = $request->base_group;
			$base_setup->save();

			Toastr::success('Operation successful', 'Success');
			return redirect('base-setup');
		} catch (\Exception $e) {
			Toastr::error('Operation Failed', 'Failed');
			return redirect()->back();
		}
	}

	public function delete(Request $request)
	{

		try {
			$tables = tableList::getTableList('bloodgroup_id', $request->id);
			$tables1 = tableList::getTableList('gender_id', $request->id);
			$tables2 = tableList::getTableList('religion_id', $request->id);
			if($tables == null && $tables1 == null && $tables2 == null) {
				 AramiscBaseSetup::destroy($request->id);
				
				 Toastr::success('Operation successful', 'Success');
				 return redirect('base-setup');

			} else {
				$msg = 'This data already used in  : ' . $tables . $tables1 . $tables2 . ' Please remove those data first';
				Toastr::error($msg, 'Failed');
				return redirect()->back();
			}
		} catch (\Exception $e) {
			Toastr::error('Operation Failed', 'Failed');
			return redirect()->back();
		}
	}
}