<?php
$arUrlRewrite=array (
  35 => 
  array (
    'CONDITION' => '#^/shedules/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => '',
    'PATH' => '/shedules/index.php',
    'SORT' => 5,
  ),
  6 => 
  array (
    'CONDITION' => '#^/cabinet/confirmation/approved/([0-9]+)/#',
    'RULE' => 'id=$1&new=0',
    'ID' => NULL,
    'PATH' => '/cabinet/confirmation/index.php',
    'SORT' => 100,
  ),
  5 => 
  array (
    'CONDITION' => '#^/cabinet/confirmation/new/([0-9]+)/#',
    'RULE' => 'id=$1&new=1',
    'ID' => NULL,
    'PATH' => '/cabinet/confirmation/index.php',
    'SORT' => 100,
  ),
  7 => 
  array (
    'CONDITION' => '#^/cabinet/dealer/employees/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/dealer/employees/user_detail.php',
    'SORT' => 100,
  ),
  0 => 
  array (
    'CONDITION' => '#^/shedule/([0-9]+)/([0-9]+)/#',
    'RULE' => 'month=$1&year=$2',
    'ID' => NULL,
    'PATH' => '/shedule/index.php',
    'SORT' => 100,
  ),
  38 => 
  array (
    'CONDITION' => '#^/knoledge_base/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/knoledge_base/index.php',
    'SORT' => 100,
  ),
  37 => 
  array (
    'CONDITION' => '#^/courses/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/courses/index.php',
    'SORT' => 100,
  ),
  39 => 
  array (
    'CONDITION' => '#^/polls/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/polls/index.php',
    'SORT' => 100,
  ),
  36 => 
  array (
    'CONDITION' => '#^/news/#',
    'RULE' => '',
    'ID' => 'bitrix:news',
    'PATH' => '/news/index.php',
    'SORT' => 100,
  ),
  27 => 
  array (
    'CONDITION' => '#^/cabinet/courses/completions/table_of_contents/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/courses/completions/table_of_contents.php',
    'SORT' => 200,
  ),
  15 => 
  array (
    'CONDITION' => '#^/cabinet/dealer/reports/course_completions/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/dealer/reports/course_completions/index.php',
    'SORT' => 200,
  ),
  25 => 
  array (
    'CONDITION' => '#^/cabinet/diller/reports/course_completions/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/diller/reports/course_completions/index.php',
    'SORT' => 200,
  ),
  12 => 
  array (
    'CONDITION' => '#^/cabinet/dealer/employees/setted_courses/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/dealer/employees/setted_courses.php',
    'SORT' => 200,
  ),
  16 => 
  array (
    'CONDITION' => '#^/cabinet/courses/re-testing/([0-9]+)/([0-9]+)/#',
    'RULE' => 'test_id=$1&question_id=$2',
    'ID' => NULL,
    'PATH' => '/cabinet/courses/re-testing/index.php',
    'SORT' => 200,
  ),
  404 => 
  array (
    'CONDITION' => '#^/cabinet/courses/pretesting/([0-9]+)/([0-9]+)/#',
    'RULE' => 'test_id=$1&question_id=$2',
    'ID' => NULL,
    'PATH' => '/cabinet/courses/pretesting/index.php',
    'SORT' => 200,
  ),
  102 => 
  array (
    'CONDITION' => '#^/cabinet/courses/feedback_poll/new/([0-9]+)/#',
    'RULE' => 'completion_id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/courses/feedback_poll/new/index.php',
    'SORT' => 200,
  ),
  11 => 
  array (
    'CONDITION' => '#^/cabinet/dealer/employees/passing/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/dealer/employees/user_passing_courses.php',
    'SORT' => 200,
  ),
  14 => 
  array (
    'CONDITION' => '#^/cabinet/courses/testing/([0-9]+)/([0-9]+)/#',
    'RULE' => 'test_id=$1&question_id=$2',
    'ID' => NULL,
    'PATH' => '/cabinet/courses/testing/index.php',
    'SORT' => 200,
  ),
  95 => 
  array (
    'CONDITION' => '#^/cabinet/courses/scorm-re-testing/([0-9]+)/#',
    'RULE' => 'course_id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/courses/scorm-re-testing/index.php',
    'SORT' => 200,
  ),
  9 => 
  array (
    'CONDITION' => '#^/cabinet/dealer/employees/delete/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/dealer/employees/user_delete.php',
    'SORT' => 200,
  ),
  10 => 
  array (
    'CONDITION' => '#^/cabinet/dealer/employees/passed/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/dealer/employees/user_passed_courses.php',
    'SORT' => 200,
  ),
  8 => 
  array (
    'CONDITION' => '#^/cabinet/dealer/employees/edit/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/dealer/employees/user_edit.php',
    'SORT' => 200,
  ),
  101 => 
  array (
    'CONDITION' => '#^/cabinet/courses/feedback_poll/([0-9]+)/#',
    'RULE' => 'completion_id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/courses/feedback_poll/index.php',
    'SORT' => 200,
  ),
  13 => 
  array (
    'CONDITION' => '#^/cabinet/courses/completions/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/courses/completions/completion_course.php',
    'SORT' => 200,
  ),
  29 => 
  array (
    'CONDITION' => '#^/cabinet/teaching/schedules/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/teaching/courses/schedules.php',
    'SORT' => 200,
  ),
  104 => 
  array (
    'CONDITION' => '#^/cabinet/admin/applications/([0-9]+)/#',
    'RULE' => 'app_id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/admin/applications/application/index.php',
    'SORT' => 200,
  ),
  30 => 
  array (
    'CONDITION' => '#^/cabinet/teaching/schedule/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/teaching/courses/schedule.php',
    'SORT' => 200,
  ),
  18 => 
  array (
    'CONDITION' => '#^/cabinet/trainer/schedule/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/trainer/schedule/index.php',
    'SORT' => 200,
  ),
  28 => 
  array (
    'CONDITION' => '#^/cabinet/teaching/course/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/teaching/courses/course.php',
    'SORT' => 200,
  ),
  17 => 
  array (
    'CONDITION' => '#^/cabinet/trainer/course/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/trainer/course/index.php',
    'SORT' => 200,
  ),
  26 => 
  array (
    'CONDITION' => '#^/zoom/([-a-zA-Z0-9_]+)/check.php#',
    'RULE' => 'acc_code=$1',
    'ID' => NULL,
    'PATH' => '/zoom/check.php',
    'SORT' => 200,
  ),
  103 => 
  array (
    'CONDITION' => '#^/cabinet/applications/([0-9]+)/#',
    'RULE' => 'app_id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/applications/application/index.php',
    'SORT' => 200,
  ),
  31 => 
  array (
    'CONDITION' => '#^/cabinet/admin/polls/([0-9]+)/#',
    'RULE' => 'id=$1',
    'ID' => NULL,
    'PATH' => '/cabinet/admin/polls/poll.php',
    'SORT' => 200,
  ),
  100 => 
  array (
    'CONDITION' => '#^/api/scorm/([0-9]+)/([0-9]+)/#',
    'RULE' => 'course_id=$1&part=$2',
    'ID' => NULL,
    'PATH' => '/api/scorm/controller.php',
    'SORT' => 200,
  ),
);
