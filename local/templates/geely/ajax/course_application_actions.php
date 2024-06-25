<?php

use Bitrix\Main\Application;
use Models\Course;
use Notifications\SiteNotifications;
use Settings\Common;
use Teaching\CourseCompletion;
use Teaching\Courses;
use Teaching\Enrollments;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$enrolls = new Enrollments();
$completions = new CourseCompletion();
$popup_title = GetMessage('SUCCESS_APPROVE_TITLE');
$popup_body = GetMessage('SUCCESS_APPROVE_BODY');
$success = true;
//фывфыв
if((int)($request['id'])>0){

    if ($request['action']=='confirm'){
        $enr = current($enrolls->get(['ID'=>(int)$request['id']]));
        $allow = true;
        if($enr['UF_PAYMENT_FROM_BALANCE'] == 1 && Course::isPaid($enr['UF_COURSE_ID']) && !\Teaching\Courses::isAllowToEnrollByCourseAndBalance($enr['UF_COURSE_ID'], $enr['UF_USER_ID'])) {
            $allow = false;
            $popup_body = "На балансе дилера недостаточно средств для записи";
        }
        if ($allow&&(int)$enr['UF_COURSE_ID']>0&&(int)$enr['UF_USER_ID']>0) {
            $allow = \Models\Course::isAllowToEnrollUser($enr['UF_COURSE_ID'], $enr['UF_USER_ID']);
            if(!$allow)
                $popup_body = "Количество зарегистрированных на этот курс сотрудников из дилерского центра ".\Models\Dealer::getNameByUser($enr['UF_USER_ID'])." достигло лимита";
        }
        if ( $allow && (int)$enr['UF_SHEDULE_ID'] > 0 && (int)$enr['UF_USER_ID'] > 0 ){
            $allow = \Teaching\SheduleCourses::isAllowToEnrollUser($enr['UF_SHEDULE_ID'], $enr['UF_USER_ID']);
            if(!$allow)
                $popup_body = "Количество зарегистрированных на этот курс сотрудников из дилерского центра ".\Models\Dealer::getNameByUser($enr['UF_USER_ID'])." достигло лимита";
            else {
                $allow = \Teaching\SheduleCourses::getFreeApprovedPlacesBySchedule($enr['UF_SHEDULE_ID']) > 0;
                if(!$allow)
                    $popup_body = "Количество зарегистрированных на этот курс сотрудников достигло лимита";
            }
        }
        if ($allow) {
            $enrolls->approve((int)$request['id']);
        } else {
            $success = false;
            $popup_title = "Одобрение невозможно";
        }
    } else {
        $enr = $enrolls->get(['ID'=>(int)$request['id']]);
        if ($enr[0]['ID']>0) {
            $enrollment = $enr[0];
            if($enrollment['UF_IS_APPROVED']==1&&$enrollment['UF_SHEDULE_ID']>0) {
                $filter = [
                    'UF_SHEDULE_ID' => $enrollment['UF_SHEDULE_ID'],
                    'UF_USER_ID' => $enrollment['UF_USER_ID']
                ];
                $array = $completions->get($filter);
                $completion = current($array);
                if($completion['ID']>0)
                    $completions->delete($completion['ID']);
            }
            $enrolls->delete((int)$request['id']);
            $course = Courses::getById($enrollment['UF_COURSE_ID']);
            $template = Common::getDeclineEventTextMessage();
            $text = str_replace('#COURSE_NAME#', $course['NAME'], $template);
            /*\Notifications\Common::sendToUser($enrollment['UF_USER_ID'], $text);*/
            $notifications = new SiteNotifications();
            $notifications->addNotification($enrollment['UF_USER_ID'], $text);
        }
        $popup_title = GetMessage('SUCCESS_REJECT_TITLE');
        $popup_body = GetMessage('SUCCESS_REJECT_BODY');
    }
}
echo json_encode(['success' => $success, 'request' => $request, 'popup_title' => $popup_title, 'popup_body' => $popup_body]);

