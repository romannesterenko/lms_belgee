<?php

use Bitrix\Main\Application;
use Models\Course;
use Teaching\Enrollments;
use Teaching\TestDrive\Group;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$success = false;
if($request['employee']>0&&$request['id']>0) {
    $success = true;
    $enrollments = new Enrollments();
    $enrollment = $enrollments->getById($request['id']);
    $allow_to_course = true;
    $allow_to_schedule = true;
    /*if($enrollment['UF_COURSE_ID'] > 0) {
        //$allow_to_course = \Models\Course::isAllowToEnrollUser($enrollment['UF_COURSE_ID'], $request['employee']);
        //TODO Хардкод т.к. не нужно для замены проверять доступность в ДЦ
        $allow_to_course = true;
    }*/
    if($allow_to_course&&(int)$enrollment['UF_SHEDULE_ID']>0) {
        $allow_to_course = \Teaching\SheduleCourses::isAllowToEnrollUser($enrollment['UF_SHEDULE_ID'], $request['employee']);
    }
    if($allow_to_course) {
        if ($enrollment['UF_COURSE_ID'] > 0 && $enrollment['UF_USER_ID'] > 0 && \Models\Course::needCoupon($enrollment['UF_COURSE_ID'])) {
            $certificate = \Models\Certificate::getActivatedByUserAndCourse($enrollment['UF_USER_ID'], $enrollment['UF_COURSE_ID']);
            if ($certificate['ID'] > 0)
                \Models\Certificate::activate($certificate['ID'], $request['employee']);
        }
        $enrollments->setEmployeeToEnroll($enrollment['ID'], $request['employee']);
        if((int)$enrollment['UF_SHEDULE_ID']>0&&$enrollment['UF_IS_APPROVED']==1){
            $completions = new \Teaching\CourseCompletion();
            $completion = current($completions->get(['UF_SHEDULE_ID' => $enrollment['UF_SHEDULE_ID'], "UF_USER_ID" => $enrollment['UF_USER_ID']]));
            if(check_full_array($completion)){
                if(Course::isEvent($completion['UF_COURSE_ID']) && $completion['UF_SHEDULE_ID'] > 0 && $completion['UF_USER_ID'] > 0) {
                    Group::deleteEmployeeFromGroup($completion['UF_SHEDULE_ID'], $completion['UF_USER_ID']);
                }
                $completions->setEmployeeToCompletion($completion['ID'], $request['employee']);
            }
        }
    } else {
        $success = false;
        $popup_title = "Замена сотрудника невозможна";
        $popup_body = "Количество зарегистрированных на этот курс сотрудников из дилерского центра ".\Models\Dealer::getNameByUser($request['employee'])." достигло лимита";
    }
}
echo json_encode(['success' => $success, 'request' => $request, 'popup_title' => $popup_title, 'popup_body' => $popup_body]);


