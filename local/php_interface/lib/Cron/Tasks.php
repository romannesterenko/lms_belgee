<?php
namespace Cron;
use DateTime;
use Models\Course;
use Models\User;
use Notifications\EmailNotifications;
use Settings\Common;
use Teaching\Enrollments;
use Teaching\SheduleCourses;

class Tasks
{
    public static function checkNeedSender()
    {
        $today_sender = Common::getTimeToStartTodaySender();
        $n_days_sender = Common::getTimeToStartNDaysSender();
        $current_time = time();
        $need_time_today_min = strtotime(date('d.m.Y').' '.$today_sender.':00');
        $need_time_today_max = strtotime(date('d.m.Y').' '.$today_sender.':59');
        $need_time_n_days_min = strtotime(date('d.m.Y').' '.$n_days_sender.':00');
        $need_time_n_days_max = strtotime(date('d.m.Y').' '.$n_days_sender.':59');
        /*if(in_array($current_time, range($need_time_today_min, $need_time_today_max))){
            self::startSenderTodayScript();
        }*/
        if(in_array($current_time, range($need_time_n_days_min, $need_time_n_days_max))){
            self::startSenderNDaysScript();
        }

    }

    private static function startSenderTodayScript()
    {
        $need_date_from = date('Y-m-d') . ' 00:00:01';
        $need_date_to = date('Y-m-d') . ' 23:59:59';
        $schedules = SheduleCourses::getArray(
            [
                '>=PROPERTY_BEGIN_DATE' => $need_date_from,
                '<=PROPERTY_BEGIN_DATE' => $need_date_to
            ],
            ['ID', 'PROPERTY_COURSE']
        );
        $enrollments = new Enrollments();
        foreach ($schedules as $schedule){
            $enrolls = $enrollments->get(['UF_SHEDULE_ID' => $schedule['ID'], 'UF_IS_APPROVED' => 1], ['ID', 'UF_USER_ID']);
            $course = Course::find($schedule['PROPERTY_COURSE_VALUE'], ['ID', 'NAME']);

            $template_topic = Common::getTodayTemplateTopic();
            $template_message = Common::getTodayTemplateMessage();
            $template_topic = self::prepareMessage($template_topic, $course, $schedule);
            $template_message = self::prepareMessage($template_message, $course, $schedule);
            if( is_array($enrolls) && count($enrolls)>0 ) {
                foreach ($enrolls as $enroll) {
                    if( (int)$enroll['UF_USER_ID']>0 )
                        \Notifications\Common::sendToUser($enroll['UF_USER_ID'], $template_message, $template_topic);
                }
            }
        }
    }

    private static function startSenderNDaysScript()
    {
        $days_from = Common::getRemindTermin();
        $days_from = $days_from>0?$days_from:2;
        $date = new DateTime();
        $need_date = $date->modify('+'.$days_from.' days')->format('Y-m-d');
        $need_date_from = $need_date .' 00:00:01';
        $need_date_to = $need_date .' 23:59:59';
        $schedules = SheduleCourses::getArray(
            [
                '>=PROPERTY_BEGIN_DATE' => $need_date_from,
                '<=PROPERTY_BEGIN_DATE' => $need_date_to
            ],
            ['ID', 'PROPERTY_COURSE']
        );
        $enrollments = new Enrollments();
        foreach ($schedules as $schedule){
            $enrolls = $enrollments->get(['UF_SHEDULE_ID' => $schedule['ID'], 'UF_IS_APPROVED' => 1], ['ID', 'UF_USER_ID', 'UF_DATE']);
            $course = \Teaching\Courses::getById($schedule['PROPERTY_COURSE_VALUE']);
            //$course = Course::find($schedule['PROPERTY_COURSE_VALUE'], ['ID', 'NAME']);
            /*$template_topic = Common::getTemplateTopic();
            $template_message = Common::getTemplateMessage();
            $template_topic = self::prepareMessage($template_topic, $course, $schedule);
            $template_message = self::prepareMessage($template_message, $course, $schedule);*/
            if( is_array($enrolls) && count($enrolls)>0 ) {
                foreach ($enrolls as $enroll) {
                    if($enroll['UF_SHEDULE_ID']>0){
                        $schedule = current(SheduleCourses::getById($enroll['UF_SHEDULE_ID']));
                    }
                    $user = User::find($enroll['UF_USER_ID']);
                    $email_params = [
                        'COURSE_NAME' => $schedule['NAME']??$course['NAME'],
                        'COURSE_DATE' => (is_object($enroll['UF_DATE']))? $enroll['UF_DATE']->toString(): '',
                        'FIO' => $user['LAST_NAME']." ".$user['NAME'],
                        'PLACE' => $course['PROPERTIES']['ADDRESS'],
                    ];
                    EmailNotifications::send('COURSE_REMINDER', $user['EMAIL'], $email_params);
                }
            }
        }

    }

    public static function startSenderNHoursScript()
    {
        $hours_from = Common::getHowLongToRemindToday();
        $hours_from = $hours_from>0?$hours_from:2;
        $date = new DateTime();
        $need_date = $date->modify('+'.$hours_from.' hours')->format('Y-m-d H:i');
        $need_date_from = $need_date .':00';
        $need_date_to = $need_date .':59';
        $schedules = SheduleCourses::getArray(
            [
                '>=PROPERTY_BEGIN_DATE' => $need_date_from,
                '<=PROPERTY_BEGIN_DATE' => $need_date_to
            ],
            ['ID', 'PROPERTY_COURSE']
        );
        $enrollments = new Enrollments();
        foreach ($schedules as $schedule){
            $enrolls = $enrollments->get(['UF_SHEDULE_ID' => $schedule['ID'], 'UF_IS_APPROVED' => 1], ['ID', 'UF_USER_ID']);
            $course = Course::find($schedule['PROPERTY_COURSE_VALUE'], ['ID', 'NAME']);

            $template_topic = Common::getNHoursTemplateTopic();
            $template_message = Common::getNHoursTemplateMessage();
            $template_topic = self::prepareMessage($template_topic, $course, $schedule);
            $template_message = self::prepareMessage($template_message, $course, $schedule);
            if( is_array($enrolls) && count($enrolls)>0 ) {
                foreach ($enrolls as $enroll) {
                    if( (int)$enroll['UF_USER_ID']>0 )
                        \Notifications\Common::sendToUser($enroll['UF_USER_ID'], $template_message, $template_topic);
                }
            }
        }

    }

    private static function prepareMessage($message, $course, $schedule)
    {
        if( is_array($course) && $course['ID']>0 && is_array($schedule) && $schedule['ID']>0 ) {
            $message = str_replace('#COURSE_NAME#', $course['NAME'], $message);
            $message = str_replace('#DATE#', $schedule['PROPERTY_BEGIN_DATE_VALUE'], $message);
            $message = str_replace('#TIME#', date('H:i:s', strtotime($schedule['PROPERTY_BEGIN_DATE_VALUE'])), $message);
        }
        return $message;
    }
}