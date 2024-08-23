<?php

namespace App\Http\Controllers\Admin\Library;

use App\YearCheck;
use App\ApiBaseMethod;
use App\AramiscBookCategory;
use Illuminate\Http\Request;
use App\Rules\UniqueCategory;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Library\BooksCategoryRequest;

class AramiscBookCategoryController extends Controller
{
    public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
    }
    
    public function index()
    {
        try{
            $bookCategories = AramiscBookCategory::status()->get();
            return view('backEnd.library.bookCategoryList', compact('bookCategories'));
        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }

    public function create()
    {
        //
    }


    public function store(BooksCategoryRequest $request)
    {
        try{
            $categories = new AramiscBookCategory();
            $categories->category_name = $request->category_name;
            $categories->school_id = Auth::user()->school_id;
            if(moduleStatusCheck('University')){
                $categories->un_academic_id = getAcademicId();
            }else{
                $categories->academic_id = getAcademicId();
            }
            $categories->save();

            Toastr::success('Operation successful', 'Success');
            return redirect('book-category-list');

        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }


    public function edit($id)
    {

        try{
            // $editData = AramiscBookCategory::find($id);
            $editData = AramiscBookCategory::status()->find($id);
            $bookCategories = AramiscBookCategory::where('school_id',Auth::user()->school_id)->get();
            return view('backEnd.library.bookCategoryList', compact('bookCategories', 'editData'));
        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(BooksCategoryRequest $request, $id)
    {
       
        try{
             $categories =  AramiscBookCategory::find($id);             
             $categories->category_name = $request->category_name;
             $categories->update();
            Toastr::success('Operation successful', 'Success');
            return redirect('book-category-list');
        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }


    public function destroy($id)
    {

        $tables = \App\tableList::getTableList('book_category_id', $id);
        $tables1 = \App\tableList::getTableList('sb_category_id', $id);
        try {
            if ($tables==null && $tables1==null) {
                 AramiscBookCategory::status()->find($id)->delete();
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            }else{
                 $msg = 'This data already used in  : ' . $tables . $tables1 . ' Please remove those data first';
                Toastr::error( $msg, 'Failed');
                return redirect()->back();
            }

        } catch (\Illuminate\Database\QueryException $e) {

            $msg = 'This data already used in  : ' . $tables . $tables1 . ' Please remove those data first';
            Toastr::error( $msg, 'Failed');
            return redirect()->back();
        }

    }

    public function deleteBookCategoryView(Request $request, $id)
    {
        try{
            $title = "Are you sure to detete this Book category?";
            $url = url('delete-book-category/' . $id);

            return view('backEnd.modal.delete', compact('id', 'title', 'url'));

        }catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }


    }

    public function deleteBookCategory($id)
    {

        $tables = \App\tableList::getTableList('book_category_id', $id);
        try {
            if ($tables==null) {
                 AramiscBookCategory::status()->find($id)->delete();
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