<?php

use Bitrix\Main\Application;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
$request = Application::getInstance()->getContext()->getRequest()->getValues();?>
<?php $APPLICATION->IncludeComponent("lms:course_shedule.card",
    "detail",
    array(
        "SHEDULE_ID" => $request['id'],
    ),
    false
);?>

