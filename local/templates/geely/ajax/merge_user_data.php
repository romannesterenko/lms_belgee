<?php

use Bitrix\Main\Application;
use Models\User;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;
$response = [];
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$response['request'] = $request;
if((int)$request['from_user_id']>0&&(int)$request['to_user_id']>0){
    $response['from_completions'] = $from_completions = (new \Teaching\CourseCompletion())->getAllByUser((int)$request['from_user_id']);
    $response['to_completions'] = $to_completions = (new \Teaching\CourseCompletion())->getAllByUser((int)$request['to_user_id']);
    foreach ($from_completions as $from_completion){
        foreach ($to_completions as $to_completion){
            if((int)$from_completion['UF_SHEDULE_ID']>0&&$from_completion['UF_SHEDULE_ID']==$to_completion['UF_SHEDULE_ID']){
                (new \Teaching\CourseCompletion())->setArchived($to_completion['ID']);
            }
        }
        $fields = [
            'UF_USER_ID' => (int)$request['to_user_id']
        ];
        (new \Teaching\CourseCompletion())->update($from_completion['ID'], $fields);
        $cert = \Models\Certificate::getActivatedByUserAndCourse($from_completion['UF_USER_ID'], $from_completion['UF_COURSE_ID']);
        if(check_full_array($cert)&&$cert['ID']>0){
            \Models\Certificate::setUser($cert['ID'], $request['to_user_id']);
        }
    }
    $response['from_enrolls'] = $from_enrolls = (new \Teaching\Enrollments())->getAllByUser((int)$request['from_user_id']);
    $response['to_enrolls'] = $to_enrolls = (new \Teaching\Enrollments())->getAllByUser((int)$request['to_user_id']);
    foreach ($from_enrolls as $from_enroll){
        foreach ($to_enrolls as $to_enroll){
            if((int)$from_enroll['UF_SHEDULE_ID']>0&&$from_enroll['UF_SHEDULE_ID']==$to_enroll['UF_SHEDULE_ID']){
                //(new \Teaching\CourseCompletion())->delete($to_completion['ID']);
            }
        }
        $fields = [
            'UF_USER_ID' => (int)$request['to_user_id']
        ];
        (new \Teaching\Enrollments())->update($from_enroll['ID'], $fields);
        $cert = \Models\Certificate::getActivatedByUserAndCourse($from_enroll['UF_USER_ID'], $from_enroll['UF_COURSE_ID']);
        if(check_full_array($cert)&&$cert['ID']>0){
            \Models\Certificate::setUser($cert['ID'], $request['to_user_id']);
        }
    }
    User::deactivate((int)$request['from_user_id']);

}
echo json_encode($response);

