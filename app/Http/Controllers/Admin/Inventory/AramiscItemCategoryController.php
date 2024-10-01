<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\AramiscItemCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Inventory\ItemCategoryRequest;

class AramiscItemCategoryController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
	}

    public function index(Request $request)
    {
        try{
            $itemCategories = AramiscItemCategory::where('school_id',Auth::user()->school_id)->get();      
            return view('backEnd.inventory.itemCategoryList', compact('itemCategories'));
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }

    public function store(ItemCategoryRequest $request)
    {
        try{
            $categories = new AramiscItemCategory();
            $categories->category_name = $request->category_name;
            $categories->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $categories->un_academic_id = getAcademicId();
            }else{
                $categories->academic_id = getAcademicId();
            }
            $categories->save();

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }

    public function edit(Request $request, $id)
    {
        try{
            if (checkAdmin()) {
                $editData = AramiscItemCategory::find($id);
            }else{
                $editData = AramiscItemCategory::where('id',$id)->where('school_id',Auth::user()->school_id)->first();
            }
            $itemCategories = AramiscItemCategory::where('school_id',Auth::user()->school_id)->get();
            return view('backEnd.inventory.itemCategoryList', compact('itemCategories', 'editData'));
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }

    public function update(Request $request, $id)
    {

        try{
            if (checkAdmin()) {
                $categories = AramiscItemCategory::find($id);
            }else{
                $categories = AramiscItemCategory::where('id',$id)->where('school_id',Auth::user()->school_id)->first();
            }
            $categories->category_name = $request->category_name;
            if(moduleStatusCheck('University')){
                $categories->un_academic_id = getAcademicId();
            }
            $categories->update();

            Toastr::success('Operation successful', 'Success');
            return redirect('item-category');
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }

    public function deleteItemCategoryView(Request $request, $id)
    {
        try{
            $title = __('common.are_you_sure_to_detete_this_item');
            $url = route('delete-item-category',$id);
            return view('backEnd.modal.delete', compact('id', 'title', 'url'));
        }catch (\Exception $e) {
           Toastr::error('Operation Failed', 'Failed');
           return redirect()->back();
        }
    }

    public function deleteItemCategory(Request $request, $id)
    {
        $tables = \App\tableList::getTableList('item_category_id', $id);
        try {
            if ($tables==null) {
                if (checkAdmin()) {
                   AramiscItemCategory::destroy($id);
                }else{
                   AramiscItemCategory::where('id',$id)->where('school_id',Auth::user()->school_id)->delete();
                }
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
          }
    }
}