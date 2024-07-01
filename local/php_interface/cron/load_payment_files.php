<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__).'/../../..');
$_SERVER["REMOTE_ADDR"] = $_SERVER["DOCUMENT_ROOT"];
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$end_date = date('Y-m-d', strtotime('yesterday'));
$schedules = \Teaching\SheduleCourses::getArray(['PROPERTY_END_DATE'=>$end_date]);
$list = (new \Teaching\CourseCompletion())->get(["UF_SHEDULE_ID" => array_keys($schedules)]);

foreach ($list as $item) {
    if($item["UF_WAS_ON_COURSE"] == 1 || $item["UF_DIDNT_COM"] == 1) {
        if (\Models\Course::isPaid($item["UF_COURSE_ID"]) && $item['UF_PAYMENT_FROM_BALANCE'] == 1) {
            \Models\Invoice::setPaid($item["ID"]);
        }
    }
}
