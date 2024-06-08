<?php

use Bitrix\Main\Application;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$list = \Teaching\SheduleCourses::getArray(
    [
        'PROPERTY_COURSE' => (int)$request['course'],
        '>=PROPERTY_BEGIN_DATE' => date('Y-m-d H:i:s', strtotime('-24 hours')),
    ]);

echo json_encode(['request' => $request, 'list' => $list, 'count' => count($list)]);

