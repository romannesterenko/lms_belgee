<?php

use Bitrix\Main\Application;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

global $USER;
$response = [];
$request = Application::getInstance()->getContext()->getRequest()->getValues();

$response['request'] = $request;
$response['message'] = 'Прохождение для данного пользователя не найдено';
$response['success'] = false;

if((int)$request['to_user_id']>0){
    $completion = current((new \Teaching\CourseCompletion())->get([
        'UF_USER_ID' => (int)$request['to_user_id'],
        'UF_COURSE_ID' => (int)$request['course'],
    ]));
    if($completion['ID']>0) {
        $old_attempts = (int)$completion['UF_TOTAL_ATTEMPTS'];
        if ($old_attempts==0)
            $old_attempts = (int)\Models\Course::getTotalAttempts((int)$request['course'])>0?(int)\Models\Course::getTotalAttempts((int)$request['course']):1;
        $new_attempts = $old_attempts+(int)$request['count'];
        (new \Teaching\CourseCompletion())->update($completion['ID'], ['UF_TOTAL_ATTEMPTS' => $new_attempts, 'UF_FAILED' => false]);
        $response['message'] = 'Успешно добавлено';
        $response['success'] = true;
    }

}
echo json_encode($response);

