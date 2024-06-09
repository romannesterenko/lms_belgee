<?php
const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application;
use Notifications\SiteNotifications;
use Teaching\CourseCompletion;
use Teaching\Courses;
use Teaching\Enrollments;
use Teaching\SheduleCourses;

$request = Application::getInstance()->getContext()->getRequest()->getValues();
//test commit
$response['request'] = $request;
if($request['employee_id']>0&&$request['course_id']>0) {
    $send = false;
    $allow_to_course = true;
    $allow_to_schedule = true;
    $allow = true;
    if(\Models\Course::isHasMaxUsers($request['course_id'])){
        $allow_to_course = \Models\Course::isAllowToEnrollUser($request['course_id'], $request['employee_id']);
    }
    //$before_courses = \Models\Course::getBeforeCourses($request['course_id']);
    if (!$allow_to_course) {
        $response['success'] = false;
        $response['message'] = "Запись невозможна. Количество зарегистрированных на этот курс сотрудников из дилерского центра ".\Models\Dealer::getNameByUser($request['employee_id'])." достигло лимита";
    } else {
        if ($request['need_coupon'] == 'Y') {
            if (Courses::checkCoupon($request['course_id'], $request['promo'], $request['employee_id'])) {
                $send = true;
            } else {
                $response['success'] = false;
                $response['message'] = GetMessage('COUPON_NOT_VERIFIED');
            }
        } else {
            $send = true;
        }
        if ($send) {
            if ($request['schedule_id'] > 0) {
                $allow_to_schedule = SheduleCourses::isAllowToEnrollUser($request['schedule_id'], $request['employee_id']);
                if(!$allow_to_schedule){
                    $response['success'] = false;
                    $response['message'] = "Запись невозможна. Количество зарегистрированных на эту дату сотрудников из дилерского центра ".\Models\Dealer::getNameByUser($request['employee_id'])." достигло лимита";
                } else {
                    $schedule_array = current(SheduleCourses::getById($request['schedule_id']));
                    if ((int)$schedule_array['PROPERTIES']['LIMIT']==0 || SheduleCourses::getFreePlacesBySchedule($request['schedule_id']) > 0) {
                        $completion = new CourseCompletion();
                        $enroll = new Enrollments();
                        $enroll->create($request);
                        $completion->create($request);
                        $notifications = new SiteNotifications();
                        $course = Courses::getById($request['course_id']);
                        $text = $topic =  GetMessage('COURSE_WAS_SETTED') . $course['NAME'] . '.';
                        $text_schedule = SheduleCourses::getTextToEmail($request['schedule_id']);
                        if($text_schedule!='')
                            $text.="<br /><br />".$text_schedule;
                        \Notifications\Common::sendToUser($request['employee_id'], $text, $topic);
                        $notifications->addNotification($request['employee_id'], $text, 'notify', '/courses/' . $course['CODE'] . '/');
                        $response['title'] = GetMessage('SUCCESS_TITLE');
                        $response['body'] = GetMessage('SUCCESS_BODY');
                        $response['success'] = true;
                    } else {
                        $response['success'] = false;
                        //$response['place'] = SheduleCourses::getFreePlacesBySchedule($request['schedule_id']);
                        $response['message'] = GetMessage('NO_PLACES');
                    }
                }
            } else {
                $need_courses_before = \Teaching\Courses::getCoursesBefore($request['course_id']);
                $completed_before_courses = true;
                if(check_full_array($need_courses_before)){
                    foreach ($need_courses_before as $need_courses_before_id) {
                        if($completed_before_courses)
                            $completed_before_courses = (new CourseCompletion())->isCompleted($need_courses_before_id, $request['employee_id']);
                    }
                }
                $completion = new CourseCompletion();
                $enroll = new Enrollments();
                $enroll->create($request);
                $completion->create($request);
                $notifications = new SiteNotifications();
                $course = Courses::getById($request['course_id']);
                $text = GetMessage('COURSE_WAS_SETTED') . $course['NAME'] . '.';
                \Notifications\Common::sendToUser($request['employee_id'], $text);
                $notifications->addNotification($request['employee_id'], $text, 'notify', '/courses/' . $course['CODE'] . '/');
                $response['title'] = GetMessage('SUCCESS_TITLE');
                $response['body'] = GetMessage('SUCCESS_BODY');
                $response['success'] = true;
            }
        } else {
            $response['success'] = false;
        }
    }
    echo json_encode($response);
}

