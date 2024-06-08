<?php

use Bitrix\Main\Application;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
$request = Application::getInstance()->getContext()->getRequest()->getValues();
if($request['date']!=0) {
    $array = explode('.', $request['date']);
    $month = $array[0];
    $year = $array[1];
}else{
    $month = 0;
    $year = 0;
}
$APPLICATION->IncludeComponent("lms:shedule.courses.list",
    "calendar_list",
    array(
        "MONTH" => $month,
        "YEAR" => $year,
        "FOR_ROLE" => (int)$_REQUEST['role']>0,
        "PAGE_COUNT" => 3,
    ),
    false
);

