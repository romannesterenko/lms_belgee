<?php

define("NO_KEEP_STATISTIC", true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;
$response = [];
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getValues();
$response['request'] = $request;
$response['course'] = current(Models\Course::getList(['ID' => $request['course_id']], ['ID', 'NAME', 'PROPERTY_SCORM']));
$response['count'] = count($response['course']['PROPERTY_SCORM_VALUE']);
echo json_encode($response);

