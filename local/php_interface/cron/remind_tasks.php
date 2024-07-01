<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__).'/../../..');
$_SERVER["REMOTE_ADDR"] = $_SERVER["DOCUMENT_ROOT"];
$_SERVER["REQUEST_METHOD"] = 'GET';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
use Helpers\RemainTasks;
use Models\Course;
use Notifications\EmailNotifications;
use Settings\Common;
use Teaching\SheduleCourses;
$tasks = RemainTasks::get(['<UF_TIME' => date('d.m.Y H:i:s'), 'UF_COMPLETED' => false]);
if (check_full_array($tasks)) {
    foreach ($tasks as $task) {
        $sended = false;
        if (SheduleCourses::getFreePlacesBySchedule($task['UF_SHEDULE_ID']) > 0) {
            if (Course::isOP($task['UF_COURSE_ID'])) {
                $emails = Common::get('emails_to_remain_free_places_op');
            } elseif(Course::isMarketing($task['UF_COURSE_ID'])) {
                $emails = Common::get('emails_to_remain_free_places_marketing');
            } else {
                $emails = Common::get('emails_to_remain_free_places_ppo');
            }
            $explode_emails = explode(',', $emails);
            if (check_full_array($explode_emails)) {
                $schedule = current(SheduleCourses::getArray(['ID' => $task['UF_SHEDULE_ID']], ['ID', "NAME", "PROPERTY_BEGIN_DATE"]));
                foreach ($explode_emails as $email) {
                    $fields = [
                        'COURSE_NAME' => $schedule['NAME'],
                        'COURSE_DATE' => $schedule['PROPERTY_BEGIN_DATE_VALUE']
                    ];
                    EmailNotifications::send('REMAIN_FREE_PLACES', trim($email), $fields);
                    $sended = true;
                }

            }
        }
        RemainTasks::setCompleted($task['ID'], $sended);
    }
}