<?php

namespace App\Http\Controllers\api;

use App\AramiscSupplier;
use App\ApiBaseMethod;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;

class ApiAramiscSupplierController extends Controller
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
            $suppliers = AramiscSupplier::all();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($suppliers, null);
            }
            return view('backEnd.inventory.supplierList', compact('suppliers'));
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
            'company_name' => "required|max:200",
            'company_address' => "required",
            'contact_person_name' => "required|max:200",
            'contact_person_mobile' => "required|numeric",
            'contact_person_email' => "email"
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
            $suppliers = new AramiscSupplier();
            $suppliers->company_name = $request->company_name;
            $suppliers->company_address = $request->company_address;
            $suppliers->contact_person_name = $request->contact_person_name;
            $suppliers->contact_person_mobile = $request->contact_person_mobile;
            $suppliers->contact_person_email = $request->contact_person_email;
            $suppliers->description = $request->description;
            // $suppliers->created_by = Auth()->user()->id;
            $results = $suppliers->save();
    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'New Supplier has been added successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($results) {
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
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        
        try{
            $editData = AramiscSupplier::find($id);
            $suppliers = AramiscSupplier::all();
    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['editData'] = $editData->toArray();
                $data['suppliers'] = $suppliers->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.inventory.supplierList', compact('editData', 'suppliers'));
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back(); 
        }
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
            'company_name' => "required|max:200",
            'company_address' => "required",
            'contact_person_name' => "required|max:200",
            'contact_person_mobile' => "required|numeric",
            'contact_person_email' => "email"
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
            $suppliers = AramiscSupplier::find($id);
            $suppliers->company_name = $request->company_name;
            $suppliers->company_address = $request->company_address;
            $suppliers->contact_person_name = $request->contact_person_name;
            $suppliers->contact_person_mobile = $request->contact_person_mobile;
            $suppliers->contact_person_email = $request->contact_person_email;
            $suppliers->description = $request->description;
            $suppliers->updated_by = Auth()->user()->id;
            $results = $suppliers->update();
    
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null,  'Supplier has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($results) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('suppliers');
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
    public function destroy($id)
    {
        //
    }

    public function deleteSupplierView(Request $request, $id)
    {
        
        try{
            $title = "Are you sure to detete this Supplier?";
            $url = url('delete-supplier/' . $id);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($id, null);
            }
            return view('backEnd.modal.delete', compact('id', 'title', 'url'));
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back(); 
        }
    }

    public function deleteSupplier(Request $request, $id)
    {
        try{
        $tables = \App\tableList::getTableList('supplier_id',$id);
        try {
            $result = AramiscSupplier::destroy($id);
            if ($result) {

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($result) {
                        return ApiBaseMethod::sendResponse(null, 'Supplier Category has been deleted successfully');
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
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
            Toastr::error('This item already used', 'Failed');
            return redirect()->back();
           }
        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
