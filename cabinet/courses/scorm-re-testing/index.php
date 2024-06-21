<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
use Bitrix\Main\Localization\Loc;
use Models\User;
use Teaching\Tests;
$status = \Models\Course::getStatus($_REQUEST['course_id']);
if($status=='expired') {
    $result = (new \Teaching\CourseCompletion)->add([
        'UF_USER_ID' => User::getCurrentId(),
        'UF_COURSE_ID' => $_REQUEST['course_id'],
        'UF_DATE_CREATE' => date('d.m.Y H:i:s'),
        'UF_DATE' => date('d.m.Y'),
        'UF_RETEST' => true,
        'UF_TOTAL_ATTEMPTS' => 1,
        'UF_IS_COMPLETE' => false,
    ]);
    header("Location: /cabinet/courses/completions/".$_REQUEST['course_id']."/");
    exit;
} else {
    $course = \Models\Course::find($_REQUEST['course_id'], ['CODE']);
    header("Location: /courses/".$course['CODE']."/");
    exit;
}?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>