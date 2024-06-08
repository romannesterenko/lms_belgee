<?php

use Helpers\UserHelper;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if($_REQUEST['user_id']>0)
    $response['success'] = UserHelper::updateFields($_REQUEST['user_id'], $_REQUEST);
$response['request'] = $_REQUEST;
echo json_encode($response);


