<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\AramiscNews;
use App\AramiscPage;
use App\AramiscCourse;
use App\AramiscNewsCategory;
use App\AramiscCourseCategory;
use App\AramiscHeaderMenuManager;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Larabuild\Pagebuilder\Models\Page;

class AramiscHeaderMenuManagerController extends Controller
{
    
    public function index()
    {
        try {
            if(activeTheme() != 'edulia'){
                $pages = AramiscPage::where('school_id', app('school')->id)->where('is_dynamic', 1)->get();
                $static_pages = AramiscPage::where('school_id', app('school')->id)->where('is_dynamic', 0)->get();
                $courses = AramiscCourse::where('school_id', app('school')->id)->get();
                $courseCategories = AramiscCourseCategory::where('school_id', app('school')->id)->get();
                $news = AramiscNews::where('school_id', app('school')->id)->get();
                $news_categories = AramiscNewsCategory::where('school_id', app('school')->id)->get();
                $menus = AramiscHeaderMenuManager::with('childs')->where('school_id', app('school')->id)->where('theme', 'default')->where('parent_id', null)->orderBy('position')->get();
                return view('backEnd.frontSettings.headerMenuManager', compact('pages', 'static_pages', 'courses', 'courseCategories', 'news_categories', 'news', 'menus'));
            }else{
                $themeMenuManage = new ThemeBasedMenuManagerController();
                return $themeMenuManage->index();
            }
        } catch (\Exception $e) {
            return response('error');
        }
    }

    public function store(Request $request)
    {
       
        try {
            if(activeTheme() != 'edulia'){
                if ($request->type == "dPages") {
                    foreach ($request->element_id as $data) {
                        $dpage = AramiscPage::findOrFail($data);
                        AramiscHeaderMenuManager::create([
                            'title' => $dpage->title,
                            'type' => $request->type,
                            'element_id' => $data,
                            'link' => $dpage->slug,
                            'position' => 387437,
                            'theme' => 'default',
                            'school_id' => app('school')->id,
                        ]);
                    }
                } elseif ($request->type == "sPages") {
                    foreach ($request->element_id as $data) {
                        $spage = AramiscPage::findOrFail($data);
                        AramiscHeaderMenuManager::create([
                            'title' => $spage->title,
                            'type' => $request->type,
                            'element_id' => $data,
                            'link' => $spage->slug,
                            'position' => 387437,
                            'theme' => 'default',
                            'school_id' => app('school')->id,
                        ]);
                    }
                } elseif ($request->type == "dCourse") {
                    foreach ($request->element_id as $data) {
                        $spage = AramiscCourse::findOrFail($data);
                        AramiscHeaderMenuManager::create([
                            'title' => $spage->title,
                            'type' => $request->type,
                            'element_id' => $data,
                            'position' => 387437,
                            'theme' => 'default',
                            'school_id' => app('school')->id,
                        ]);
                    }
                } elseif ($request->type == "dCourseCategory") {
                    foreach ($request->element_id as $data) {
                        $spage = AramiscCourseCategory::findOrFail($data);
                        AramiscHeaderMenuManager::create([
                            'title' => $spage->category_name,
                            'type' => $request->type,
                            'element_id' => $data,
                            'position' => 387437,
                            'theme' => 'default',
                            'school_id' => app('school')->id,
                        ]);
                    }
                } elseif ($request->type == "dNews") {
                    foreach ($request->element_id as $data) {
                        $dNews = AramiscNews::findOrFail($data);
                        AramiscHeaderMenuManager::create([
                            'title' => $dNews->news_title,
                            'type' => $request->type,
                            'element_id' => $data,
                            'position' => 387437,
                            'theme' => 'default',
                            'school_id' => app('school')->id,
                        ]);
                    }
                } elseif ($request->type == "dNewsCategory") {
                    foreach ($request->element_id as $data) {
                        $dNewsCategory = AramiscNewsCategory::findOrFail($data);
                        AramiscHeaderMenuManager::create([
                            'title' => $dNewsCategory->category_name,
                            'type' => $request->type,
                            'element_id' => $data,
                            'position' => 387437,
                            'theme' => 'default',
                            'school_id' => app('school')->id,
                        ]);
                    }
                } elseif ($request->type == "customLink") {
                    AramiscHeaderMenuManager::create([
                        'title' => $request->title,
                        'link' => $request->link,
                        'type' => $request->type,
                        'position' => 387437,
                        'theme' => 'default',
                        'school_id' => app('school')->id,
                    ]);
                }
            }else{
                $themeMenuManage = new ThemeBasedMenuManagerController();
                $themeMenuManage->store($request);
            }
            return $this->reloadWithData();
        } catch (\Exception $e) {
            return response('error');
        }
    }

    public function update(Request $request)
    {
        
        try {
            if(activeTheme() != 'edulia'){
                if ($request->type == "dPages") {
                    AramiscHeaderMenuManager::where('id', $request->id)->update([
                        'title' => $request->title,
                        'type' => $request->type,
                        'element_id' => $request->page,
                        'show' => $request->content_show,
                        'is_newtab' => $request->is_newtab,
                        'school_id' => app('school')->id,
                    ]);
                } elseif ($request->type == "sPages") {
                    AramiscHeaderMenuManager::where('id', $request->id)->update([
                        'title' => $request->title,
                        'type' => $request->type,
                        'element_id' => $request->static_pages,
                        'show' => $request->content_show,
                        'is_newtab' => $request->is_newtab,
                        'school_id' => app('school')->id,
                    ]);
                } elseif ($request->type == "dCourse") {
                    AramiscHeaderMenuManager::where('id', $request->id)->update([
                        'title' => $request->title,
                        'type' => $request->type,
                        'element_id' => $request->course,
                        'show' => $request->content_show,
                        'is_newtab' => $request->is_newtab,
                        'school_id' => app('school')->id,
                    ]);
                } elseif ($request->type == "dCourseCategory") {
                    AramiscHeaderMenuManager::where('id', $request->id)->update([
                        'title' => $request->title,
                        'type' => $request->type,
                        'element_id' => $request->course_category,
                        'show' => $request->content_show,
                        'is_newtab' => $request->is_newtab,
                        'school_id' => app('school')->id,
                    ]);
                } elseif ($request->type == "dNews") {
                    AramiscHeaderMenuManager::where('id', $request->id)->update([
                        'title' => $request->title,
                        'type' => $request->type,
                        'element_id' => $request->news,
                        'show' => $request->content_show,
                        'is_newtab' => $request->is_newtab,
                        'school_id' => app('school')->id,
                    ]);
                } elseif ($request->type == "dNewsCategory") {
                    AramiscHeaderMenuManager::where('id', $request->id)->update([
                        'title' => $request->title,
                        'type' => $request->type,
                        'element_id' => $request->news_category,
                        'show' => $request->content_show,
                        'is_newtab' => $request->is_newtab,
                        'school_id' => app('school')->id,
                    ]);
                } elseif ($request->type == "customLink") {
                    AramiscHeaderMenuManager::where('id', $request->id)->update([
                        'title' => $request->title,
                        'link' => $request->link,
                        'type' => $request->type,
                        'show' => $request->content_show,
                        'is_newtab' => $request->is_newtab,
                        'school_id' => app('school')->id,
                    ]);
                }
            }else{
                $themeMenuManage = new ThemeBasedMenuManagerController();
                $themeMenuManage->update($request);
            }
            return $this->reloadWithData();
        } catch (\Exception $e) {
            return response('error');
        }
    }

    public function delete(Request $request)
    {
        try {
            $element = AramiscHeaderMenuManager::find($request->id);
            if (count($element->childs) > 0) {
                foreach ($element->childs as $child) {
                    $child->update(['parent_id' => $element->parent_id]);
                }
            }
            $element->delete();
            return $this->reloadWithData();
        } catch (\Exception $e) {
            return response('error');
        }
    }

    public function reordering(Request $request)
    {
        $menuItemOrder = json_decode($request->get('order'));
        $this->orderMenu($menuItemOrder, null);
        return true;
    }

    private function orderMenu(array $menuItems, $parentId)
    {
        foreach ($menuItems as $index => $item) {

            $menuItem = AramiscHeaderMenuManager::findOrFail($item->id);
            $menuItem->update([
                'position' => $index + 1,
                'parent_id' => $parentId,
            ]);
            if (isset($item->children)) {
                $this->orderMenu($item->children, $menuItem->id);
            }
        }
    }


    private function reloadWithData()
    {
        if(activeTheme() != 'edulia'){
            $pages = AramiscPage::where('is_dynamic', 1)->where('school_id', app('school')->id)->get();
            $static_pages = AramiscPage::where('is_dynamic', 0)->where('school_id', app('school')->id)->get();
            $courses = AramiscCourse::where('school_id', app('school')->id)->get();
            $courseCategories = AramiscCourseCategory::where('school_id', app('school')->id)->get();
            $news = AramiscNews::where('school_id', app('school')->id)->get();
            $news_categories = AramiscNewsCategory::where('school_id', app('school')->id)->get();
            $menus = AramiscHeaderMenuManager::with('childs')->where('parent_id', null)->where('school_id', app('school')->id)->where('theme', 'default')->orderBy('position')->get();
            return view('backEnd.frontSettings.headerSubmenuList', compact('pages', 'static_pages', 'courses', 'courseCategories', 'news_categories', 'news', 'menus'));
        }else{
            $themeMenuManage = new ThemeBasedMenuManagerController();
            return $themeMenuManage->renderData();
        }
    }
}