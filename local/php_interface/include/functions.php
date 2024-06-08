<?php

use danog\MadelineProto\Logger;
use Notifications\Common as Notification;

//дебаг
function dd($arr){
    echo("<script>console.log(JSON.parse('" . json_encode($arr) . "'))</script>");
}
function dump($arr){
    global $USER;
    if(($USER->IsAuthorized()&&$USER->GetID()==2)||$_REQUEST['dbg'] == "Y") {
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
    }
}

function dduser($arr, $user_id = 0){
    global $USER;
    if($user_id == 0)
        return '';
    if(($USER->IsAuthorized()&&$USER->GetID()==$user_id)) {
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
        die();
    }
}

function dump_request(){
    global $USER;
    if($USER->IsAuthorized()&&$USER->GetID()==2) {
        echo "<pre>";
        print_r($_REQUEST);
        echo "</pre>";
    }
}
function dumpdie($arr){
    global $USER;
    if($USER->IsAuthorized()&&$USER->GetID()==2) {
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
    }
    die();
}
function add2Log($data){
    define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/log.txt");
    AddMessage2Log($data);
}
function check_full_array($arr):bool{
    return is_array($arr)&&count($arr)>0;
}
function getOneTask(){
    $tasks = new \Helpers\Tasks();

    $tasks->processOne(1);
    return 'getOneTask();';
}

function checkTgLoop(){
    $settings = new danog\MadelineProto\Settings;
    $settings->getLogger()->setLevel(Logger::FATAL_ERROR);
    $session_path = $_SERVER["DOCUMENT_ROOT"] . \Settings\Common::get('telegram_session_path');
    $MadelineProto = new \danog\MadelineProto\API($session_path);
    $MadelineProto->async(false);
    \Telegram\TelegramHandler::startAndLoop($session_path, $settings);
}

function checkExpiredEnrollments(){
    $enrollments = new \Teaching\Enrollments();
    foreach ($enrollments->getExpired() as $enroll){
        $user_id = $enroll['UF_USER_ID'];
        $enrollments->delete($enroll['ID'], true);
        if($user_id>0){
            $course = \Models\Course::find($enroll['UF_COURSE_ID'], ['ID', 'NAME']);
            $template = \Settings\Common::getDeleteExpireEventTextMessage();
            $text = str_replace('#COURSE_NAME#', $course['NAME'], $template);
            Notification::sendToUser($user_id, $text);
        }
    }
    return 'checkExpiredEnrollments();';
}

function remindAboutCourses(){
    $days_from = \Settings\Common::getRemindTermin();
    $date = new DateTime();
    $need_date = $date->modify('+'.$days_from.' days')->format('Y-m-d');
    $need_date_from = $need_date .' 00:00:01';
    $need_date_to = $need_date .' 23:59:59';
    $schedules = \Teaching\SheduleCourses::getArray(
        [
            '>=PROPERTY_BEGIN_DATE' => $need_date_from,
            '<=PROPERTY_BEGIN_DATE' => $need_date_to
        ],
        ['ID', 'PROPERTY_COURSE']
    );
    $enrollments = new \Teaching\Enrollments();
    foreach ($schedules as $schedule){
        $enrolls = $enrollments->get(['UF_SHEDULE_ID' => $schedule['ID'], 'UF_IS_APPROVED' => 1], ['ID', 'UF_USER_ID']);
        $course = \Models\Course::find($schedule['PROPERTY_COURSE_VALUE'], ['ID', 'NAME']);
        $template_topic = \Settings\Common::getTemplateTopic();
        $template_message = \Settings\Common::getTemplateMessage();
        if( is_array($course) && $course['ID'] ){
            $template_topic = str_replace('#COURSE_NAME#', $course['NAME'], $template_topic);
            $template_topic = str_replace('#DATE#', $schedule['PROPERTY_BEGIN_DATE_VALUE'], $template_topic);
            $template_message = str_replace('#COURSE_NAME#', $course['NAME'], $template_message);
            $template_message = str_replace('#DATE#', $schedule['PROPERTY_BEGIN_DATE_VALUE'], $template_message);
        }
        if( is_array($enrolls) && count($enrolls)>0 ) {
            foreach ($enrolls as $enroll) {
                if( (int)$enroll['UF_USER_ID']>0 )
                    \Notifications\Common::sendToUser($enroll['UF_USER_ID'], $template_message, $template_topic);
            }
        }
    }
}

function remindAboutCourseToday(){
    $days_from = \Settings\Common::getRemindTermin();
    $need_date_from = date('Y-m-d') .' 00:00:01';
    $need_date_to = date('Y-m-d') .' 23:59:59';
    $schedules = \Teaching\SheduleCourses::getArray(
        [
            '>=PROPERTY_BEGIN_DATE' => $need_date_from,
            '<=PROPERTY_BEGIN_DATE' => $need_date_to
        ],
        ['ID', 'PROPERTY_COURSE']
    );
    $enrollments = new \Teaching\Enrollments();
    foreach ($schedules as $schedule){
        $enrolls = $enrollments->get(['UF_SHEDULE_ID' => $schedule['ID'], 'UF_IS_APPROVED' => 1], ['ID', 'UF_USER_ID']);
        $course = \Models\Course::find($schedule['PROPERTY_COURSE_VALUE'], ['ID', 'NAME']);
        $template_topic = \Settings\Common::getTodayTemplateTopic();
        $template_message = \Settings\Common::getTodayTemplateMessage();
        if( is_array($course) && $course['ID'] ){
            $template_topic = str_replace('#COURSE_NAME#', $course['NAME'], $template_topic);
            $template_topic = str_replace('#DATE#', $schedule['PROPERTY_BEGIN_DATE_VALUE'], $template_topic);
            $template_message = str_replace('#COURSE_NAME#', $course['NAME'], $template_message);
            $template_message = str_replace('#DATE#', $schedule['PROPERTY_BEGIN_DATE_VALUE'], $template_message);
        }
        if( is_array($enrolls) && count($enrolls)>0 ) {
            foreach ($enrolls as $enroll) {
                if( (int)$enroll['UF_USER_ID']>0 )
                    \Notifications\Common::sendToUser($enroll['UF_USER_ID'], $template_message, $template_topic);
            }
        }
    }
}