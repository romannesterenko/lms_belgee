<?php

use Bitrix\Main\Application;
use Teaching\Courses;
use Teaching\Enrollments;
use Teaching\SheduleCourses;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$response['request'] = $request = Application::getInstance()->getContext()->getRequest()->getValues();
$enrollment = new Enrollments();
$allow_to_course = true;
$allow_to_schedule = true;

if(\Models\Course::isHasMaxUsers($request['id'])){
    $allow_to_course = \Models\Course::isAllowToEnrollUser($request['id'], $request['user']);
}
if(!$allow_to_course){
    $response['success'] = false;
    $response['message'] = "Запись невозможна. Количество зарегистрированных на этот курс сотрудников из дилерского центра ".\Models\Dealer::getNameByUser($request['employee_id'])." достигло лимита";
} else {
    if ($request['need_coupon'] == 'Y') {
        $response['success'] = false;
        if (Courses::checkCoupon($request['id'], $request['promo'], $request['user'])) {
            $request['with_coupon'] = true;
            $enrollment->addFromRequest($request);
            $response['title'] = GetMessage('SUCCESS_TITLE');
            $response['body'] = GetMessage('SUCCESS_BODY');
            $response['success'] = true;
        } else {
            $response['success'] = false;
            $response['message'] = GetMessage('COUPON_NOT_VERIFIED');
        }
    } else {
        if((int)$request['schedule']!=0){
            $allow_to_schedule = SheduleCourses::isAllowToEnrollUser((int)$request['schedule'], $request['user']);
        }
        if(!$allow_to_schedule){
            $response['success'] = false;
            $response['message'] = "Запись невозможна. Количество зарегистрированных на эту дату сотрудников из дилерского центра ".\Models\Dealer::getNameByUser($request['employee_id'])." достигло лимита";
        } else {
            $enrollment->addFromRequest($request);
            $response['title'] = GetMessage('SUCCESS_TITLE');
            $response['body'] = GetMessage('SUCCESS_BODY');
            $response['success'] = true;
        }
    }
}
echo json_encode($response);