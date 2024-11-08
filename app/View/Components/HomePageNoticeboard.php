<?php

namespace App\View\Components;

use App\AramiscNoticeBoard;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HomePageNoticeboard extends Component
{
    public $count;
    public $sorting;

    public function __construct($count = 5, $sorting = 'asc')
    {
        $this->count = $count;
        $this->sorting = $sorting;
    }

    public function render(): View|Closure|string
    {
        $noticeBoards = AramiscNoticeBoard::query();
        $noticeBoards = $noticeBoards->where('is_published',1)->where('school_id', app('school')->id);
        if($this->sorting =='asc'){
            $noticeBoards->orderBy('id','asc');
        }elseif($this->sorting =='desc'){
            $noticeBoards->orderBy('id','desc');
        }else{
            $noticeBoards->inRandomOrder();
        }
        $noticeBoards = $noticeBoards->take($this->count)->get();
        return view('components.'.activeTheme().'.home-page-noticeboard',compact('noticeBoards'));
    }
}
