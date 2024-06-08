<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;

$show_mode = true;
$courses = \Models\Course::getList(['PROPERTY_COURSE_TYPE' => 6, 'PROPERTY_SCORM' => false], ['ID', 'NAME']);
foreach($courses as $course){
    $max_points_in_course = \Models\Course::getMaxPoints($course['ID']);
    $min_points_in_course = \Models\Course::getMinPoints($course['ID']);
    $completions = (new \Teaching\CourseCompletion())->get(['UF_COURSE_ID' => $course['ID']]);
    foreach ($completions as $completion) {
        if($completion['UF_POINTS'] > $max_points_in_course){
            if($completion['UF_IS_COMPLETE']==1){
                //(new \Teaching\CourseCompletion())->setPoints($max_points_in_course, $completion['ID']);
                dump($completion['UF_POINTS'].' из '.$max_points_in_course);
                dump($completion['ID']);
                dump($course['NAME']);
                dump('updated points');
            } else {
                //(new \Teaching\CourseCompletion())->setPoints($max_points_in_course, $completion['ID']);
                dump($completion['UF_POINTS'].' из '.$max_points_in_course);
                dump($completion['ID']);
                dump($course['NAME']);
                dump('updated points not complete');
            }
        }
    }
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");