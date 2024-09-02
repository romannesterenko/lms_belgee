<?php

use Bitrix\Main\Application;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;

$response['request'] = $request = Application::getInstance()->getContext()->getRequest()->getValues();
$response['free_enroll'] = \Models\Course::allowToFreeEnroll((int)$request['course_id'], (int)$request['user_id']);

echo json_encode($response);




