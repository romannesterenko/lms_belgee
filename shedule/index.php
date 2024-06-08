<?php

use Bitrix\Main\Localization\Loc;
use Teaching\Roles;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
/**
* todo Попросили поставить, на будущий период
**/
//if(!$_GET["year"]) LocalRedirect('/shedule/03/2024/');

if (str_contains($_GET['year'], '?')){
    $year_array = explode('?', $_GET['year']);
    $_GET['year'] = $_REQUEST['year'] = $year_array[0];
}

$APPLICATION->SetTitle(Loc::getMessage('TITLE')); 
$APPLICATION->IncludeComponent("lms:shedule.calendar",
    "",
    array(
        "MONTH" => $_REQUEST['month']??date('m'),
        "YEAR" => $_REQUEST['year']??date('Y'),
        "USER" => $USER->GetID(),
    ),
    false
);
$APPLICATION->IncludeComponent("lms:shedule.courses.list",
    "calendar_list",
    array(
        "MONTH" => $_REQUEST['month']??date('m'),
        "YEAR" => $_REQUEST['year']??date('Y'),
        "FOR_ROLE" => Roles::getByCurrentUser(),
        "PAGE_COUNT" => 3,
    ),
    false
);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");