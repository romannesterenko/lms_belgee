<?php
$_SERVER["DOCUMENT_ROOT"] = '/home/u520251/lms.geely-motors.com/www';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");


$courses_list = \Models\Course::getList(['PROPERTY_HAS_RETEST' => 155], ['ID', 'NAME', 'PROPERTY_CERT_EXP']);

$array = [];

foreach ($courses_list as $course){
    $list = (new \Teaching\CourseCompletion())->get(['UF_COURSE_ID' =>$course['ID']], ['*'], ['UF_DATE' => 'DESC']);
    foreach ($list as $item) {
        $array[$item['UF_COURSE_ID']][$item['UF_USER_ID']][] = $item;
    }
}

foreach ($array as $course_id => $info){
    $expired = $courses_list[$course_id]['PROPERTY_CERT_EXP_VALUE']??12;
    foreach ($info as $user_id => $completions) {
        $last_completion = current($completions);
        if($last_completion['UF_IS_COMPLETE'] == 1 && !empty($last_completion['UF_COMPLETED_TIME'])) {
            $time = $last_completion['UF_COMPLETED_TIME'];
            $expired_date = $time->add('+' . $expired . ' months');
            $expired_date = $time->add('- '.\Settings\Common::getRetestRemindTermin().' days');
            /*if ((new DateTime())->format('Y-m-d H:i:s') >= $expired_date->format('Y-m-d H:i:s')) {
                sendNeedRetest($last_completion['ID']);
                //die();
            }*/
            if ((new DateTime())->format('Y-m-d H:i:s') >= $expired_date->format('Y-m-d 00:00:01') && (new DateTime())->format('Y-m-d H:i:s') <= $expired_date->format('Y-m-d 23:59:59')) {
                sendNeedRetest($last_completion['ID']);
            }
        }
    }
}
function sendNeedRetest($item_id): void
{
    $item = (new \Teaching\CourseCompletion())->find($item_id);
    $user = \Models\User::find($item['UF_USER_ID'], ['ID', 'NAME', 'LAST_NAME', 'EMAIL', 'UF_DEALER']);
    if(!$user['UF_DEALER'])
        return;
    $course = \Models\Course::find($item['UF_COURSE_ID'], ['NAME', 'PROPERTY_CERT_EXP']);
    $expired = $course['PROPERTY_CERT_EXP_VALUE']??12;
    $time = $item['UF_DATE'];
    if($item['UF_COMPLETED_TIME'])
        $time = $item['UF_COMPLETED_TIME'];
    $completed_date = (string)$item['UF_COMPLETED_TIME'];
    $expired_date = (string)($time->add('+' . $expired . ' months')->format('d.m.Y'));
    $re_cert_date = (string)($time->add('+1 day')->format('d.m.Y'));
    $fields = [
        'USER_NAME' => $user['NAME'] . " " . $user['LAST_NAME'],
        'COMPLETED' => (string)$completed_date,
        'CERT_DATE' => (string)$expired_date,
        'NEED_RECERT_DATE' => (string)$re_cert_date,
        'COURSE_NAME' => $course['NAME'],
        'PERIOD' => $expired." ".\Helpers\StringHelpers::plural($expired, ['месяц', 'месяца', 'месяцев']),
        'QUESTIONS_COUNT' => \Teaching\Tests::getQuestionsCntByCourse($course['ID']),
    ];
    unset($fields['COMPLETED']);
    \Notifications\EmailNotifications::send('NEED_RETEST', $user['EMAIL'], $fields);
    \Helpers\Log::writeCommon($fields, 'cron/need_retest');
}