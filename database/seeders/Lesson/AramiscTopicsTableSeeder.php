<?php

namespace Database\Seeders\Lesson;

use App\AramiscAssignSubject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Lesson\Entities\AramiscLesson;
use Modules\Lesson\Entities\AramiscLessonTopic;
use Modules\Lesson\Entities\AramiscLessonTopicDetail;

class SmTopicsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($school_id, $academic_id, $count=5)
    {
        // $topic = ['theory', 'poem', 'practical', 'others'];
        // $lesson_id = AramiscLesson::where('class_id', 1)->where('section_id', 1)->where('school_id', $school_id)->where('academic_id', $academic_id)->first()->id;
        // $assignSubject = AramiscAssignSubject::where('school_id', $school_id)
        // ->where('academic_id', $academic_id)
        // ->first();
        // $is_duplicate = AramiscLessonTopic::where('class_id', $assignSubject->class_id)->where('lesson_id', $lesson_id)->where('section_id', $assignSubject->sction_id)->where('subject_id', $assignSubject->subject_id)->first();
        // if ($is_duplicate) {
        //     $length = count($topic);
        //     for ($i = 0; $i < $length; $i++) {
        //         $topic_title = $topic[$i++];
  
        //         $topicDetail = new AramiscLessonTopicDetail;
        //         $topicDetail->topic_id = $is_duplicate->id;
        //         $topicDetail->topic_title = $topic_title ? $topic_title.'0'.$i : '0'.$i;
        //         $topicDetail->lesson_id = $lesson_id;
        //         $topicDetail->school_id = $school_id;
        //         $topicDetail->academic_id = $academic_id;
        //         $topicDetail->save();
  
        //     }
        //     DB::commit();
  
        // } else {
  
        //     $aramiscTopic = new AramiscLessonTopic;
        //     $aramiscTopic->class_id = $assignSubject->class_id;
        //     $aramiscTopic->section_id = $assignSubject->section_id;
        //     $aramiscTopic->subject_id = $assignSubject->subject_id;
        //     $aramiscTopic->lesson_id = $lesson_id;
        //     $aramiscTopic->school_id = $school_id;
        //     $aramiscTopic->academic_id = $academic_id;
        //     $aramiscTopic->save();
        //     $aramiscTopic_id = $aramiscTopic->id;
        //     $length = count($topic);
  
        //     for ($i = 0; $i < $length; $i++) {
        //         $topic_title = $topic[$i];
  
        //         $topicDetail = new AramiscLessonTopicDetail;
        //         $topicDetail->topic_id = $aramiscTopic_id;
        //         $topicDetail->topic_title = $topic_title ? $topic_title.'0'.$i : '0'.$i;
        //         $topicDetail->lesson_id = $lesson_id;
        //         $topicDetail->school_id = $school_id;
        //         $topicDetail->academic_id = $academic_id;
        //         $topicDetail->save();
  
        //     }
        //     DB::commit();
  
        // }
    }
}
