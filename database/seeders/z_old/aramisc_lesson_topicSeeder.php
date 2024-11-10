<?php

namespace Database\Seeders;

use App\AramiscAssignSubject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Lesson\Entities\AramiscLesson;
use Modules\Lesson\Entities\AramiscLessonTopic;
use Modules\Lesson\Entities\AramiscLessonTopicDetail;

class aramisc_lesson_topicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      $topic = ['theory', 'poem', 'practical', 'others'];
      $lesson_id = AramiscLesson::where('class_id', 1)->where('section_id', 1)->first()->id;
      $is_duplicate = AramiscLessonTopic::where('class_id', 1)->where('lesson_id', 1)->where('section_id', 1)->where('subject_id', 1)->first();
      if ($is_duplicate) {
          $length = count($topic);
          for ($i = 0; $i < $length; $i++) {
              $topic_title = $topic[$i++];

              $topicDetail = new AramiscLessonTopicDetail;
              $topicDetail->topic_id = $is_duplicate->id;
              $topicDetail->topic_title = $topic_title;
              $topicDetail->lesson_id = $lesson_id;
              $topicDetail->save();

          }
          DB::commit();

      } else {

          $aramiscTopic = new AramiscLessonTopic;
          $aramiscTopic->class_id = 1;
          $aramiscTopic->section_id = 1;
          $aramiscTopic->subject_id = 1;
          $aramiscTopic->lesson_id = $lesson_id;
          $aramiscTopic->save();
          $aramiscTopic_id = $aramiscTopic->id;
          $length = count($topic);

          for ($i = 0; $i < $length; $i++) {
              $topic_title = $topic[$i];

              $topicDetail = new AramiscLessonTopicDetail;
              $topicDetail->topic_id = $aramiscTopic_id;
              $topicDetail->topic_title = $topic_title;
              $topicDetail->lesson_id = $lesson_id;
              $topicDetail->school_id = 1;
              $topicDetail->academic_id = 1;
              $topicDetail->save();

          }
          DB::commit();

      }
  }
}
