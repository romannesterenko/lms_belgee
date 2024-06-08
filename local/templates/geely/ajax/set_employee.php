<?php

use Bitrix\Main\Application;
use Helpers\UserHelper;
use Notifications\SiteNotifications;
use Teaching\Courses;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$request = Application::getInstance()->getContext()->getRequest()->getValues();
if($request['employee']>0&&$request['id']>0) {
    $result = UserHelper::setUserValue('UF_REQUIRED_COURSES', [$request['id']], $request['employee']);
    if($result){
        $notifications = new SiteNotifications();
        $course = Courses::getById($request['id']);
        $text = GetMessage('COURSE_WAS_SETTED').$course['NAME'].'.';
        $notifications->addNotification($request['employee'], $text, 'notify', '/courses/'.$course['CODE'].'/');
    }
}

