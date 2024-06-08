<?php

use Bitrix\Main\Application;
use Teaching\Courses;
use Teaching\Enrollments;
use Teaching\SheduleCourses;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$enrollment = new Enrollments();
$request['is_free'] = Courses::isFreeSheduleCourse((int)$request['id']);
if($request['is_free']){
    $schedules = SheduleCourses::getByCourse((int)$request['id']);
    $schedule = array_shift($schedules);
}else {
    $schedule = SheduleCourses::getByCourseAndDate((int)$request['id'], $request['date']);
}
$request['id'] = $schedule['ID'];
$enrollment->addFromRequest($request);
$APPLICATION->IncludeComponent("lms:course.card",
    "list",
    array(
        "COURSE_ID" => $request['id'],
    ),
    false
);
