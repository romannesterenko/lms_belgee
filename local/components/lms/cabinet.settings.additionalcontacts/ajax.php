<?php

use Settings\Notifications;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;
$response['send'] = (int)$_REQUEST['send'];
$response['user'] = (int)$USER->GetID();
$notifications = new Notifications();
$notifications->changeSendNotifications($response);
$response['message'] = GetMessage('SETTING_APPLIED_SUCCESSFUL');
echo json_encode($response);
