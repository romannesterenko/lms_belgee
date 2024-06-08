<?php

use Bitrix\Main\Application;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$list = [];
$isPaid = false;
if(\Teaching\Courses::isPaid($request['course'])){
    $isPaid = true;
    $list = \Models\Certificate::getFreeByCourse($request['course']);
}
echo json_encode(['is_payment_course' => $isPaid, 'list' => $list]);

