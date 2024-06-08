<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
if((int)$_REQUEST['id']>0) {
$APPLICATION->IncludeComponent("lms:course_shedule.card",
    "detail",
    array(
        "SHEDULE_ID" => (int)$_REQUEST['id'],
    ),
    false
);
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");