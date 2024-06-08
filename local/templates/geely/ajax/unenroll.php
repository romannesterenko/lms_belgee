<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Teaching\Enrollments;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$success = false;
$message = '';
$title = '';
if($request['id']>0){
    $enroll = new Enrollments();
    $completions = new \Teaching\CourseCompletion();
    $enrollment = $enroll->find((int)$request['id']);
    if($enrollment['UF_SHEDULE_ID']>0){
        $filter = [
            'UF_SHEDULE_ID' => $enrollment['UF_SHEDULE_ID'],
            'UF_USER_ID' => $enrollment['UF_USER_ID'],
            '!UF_FAILED' => 1
        ];
        $completion = current($completions->get($filter));
        if($enroll->isAllowUnenroll($enrollment)){
            $completions->delete($completion['ID']);
            $enroll->delete($enrollment['ID']);
            $success = true;
            $title = Loc::getMessage('SUCCESS');
            $message = Loc::getMessage('SUCCESS_DELETED');
        }else{
            $title = Loc::getMessage('ERROR');
            $message = Loc::getMessage('ERROR_MESSAGE');
        }
    }else{
        $enroll->delete($enrollment['ID']);
    }

}
echo json_encode(['success'=>$success, 'title' => $title, 'body' => $message, 'request' => $request]);

