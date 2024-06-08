<?php

namespace Notifications;

use CEventMessage;
use Models\User;

class Common
{
    public static function sendToUser($user_id, $text, $topic='')
    {
        $notify_settings = new \Settings\Notifications();
		// if($notify_settings->isSendMeNotifications($user_id)){
            $email = \Models\User::getEmail($user_id);
		// if($email&&filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if($topic=='')
                    $topic = $text;
                \Notifications\EmailNotifications::sentCommonEmail($email, $topic, $text);
				//  }
            switch ($notify_settings->getSettedMethodsForUser($user_id)){
                case 4:
                    break;
                case 5:
                    break;
                case 6:
                    $login = str_replace('@', '', \Models\User::getTelegramLogin($user_id));
                    \Helpers\Tasks::setSendMessageTask($login, $text);
                    break;
            }
			//  }
    }

    public static function sendBroadcastLink($user_id, $shedule_id){
        $notify_settings = new \Settings\Notifications();
        if($notify_settings->isSendMeNotifications($user_id)){
            switch ($notify_settings->getSettedMethodsForUser($user_id)){
                case 4:
                    \Notifications\EmailNotifications::sendBroadCastLink($user_id, $shedule_id);
                    break;
                case 5:
                    //функционал отправки SMS
                    break;
                case 6:
                    $login = str_replace('@', '', \Models\User::getTelegramLogin($user_id));
                    $course = \Teaching\Courses::getByScheduleId($shedule_id);
                    $schedule = \Teaching\SheduleCourses::getById($shedule_id);
                    $message = CEventMessage::GetByID(23)->Fetch();
                    if(check_full_array($course)&&check_full_array($schedule)&&check_full_array($message)) {
                        $schedule = current($schedule);
                        $user = User::find($user_id);
                        $link = $schedule['PROPERTIES']['ZOOM_LINK']??$schedule['PROPERTIES']['TG_LINK'];
                        $text = str_replace('#FULL_NAME#', $user['NAME'].' '.$user['LAST_NAME'], $message['MESSAGE']);
                        $text = str_replace('#TIME#', $schedule['PROPERTIES']['BEGIN_DATE'], $text);
                        $text = str_replace('#COURSE_NAME#', $course['NAME'], $text);
                        $text = str_replace('#LINK#', $link, $text);
                        \Helpers\Tasks::setSendMessageTask($login, $text);
                    }
                    break;
            }
        }
    }
}