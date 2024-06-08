<?php

use Bitrix\Main\Application;
use Helpers\UserHelper;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$request = Application::getInstance()->getContext()->getRequest()->getValues();
if($request['user_id']>0&&$request['value']>0)
    UserHelper::setUserValue('UF_SEND_NOTIFIC_METHOD', $request['value'], $request['user_id']);

