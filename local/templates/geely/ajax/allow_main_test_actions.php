<?php

use Bitrix\Main\Application;
use Teaching\SheduleCourses;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;
$response = [];
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$response['request'] = $request;
if((int)$request['schedule']>0){
    if($request['checked']=='true'){
        SheduleCourses::updateAllowMainTest((int)$request['schedule'], 129);
    } else {
        SheduleCourses::updateAllowMainTest((int)$request['schedule'], false);
    }
}
echo json_encode($response);

