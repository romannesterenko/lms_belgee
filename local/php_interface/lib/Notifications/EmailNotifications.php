<?php

namespace Notifications;
use Bitrix\Main\Mail\Event;
use Models\User;

class EmailNotifications
{
    //отправка письма средствами битрикс
    private static function sendEmail(string $event, string $email, array $additional_fields=[], string $message_id="")
    {
        $fields = array_merge(["TO_EMAIL" => $email], $additional_fields);
        $params = [
            "EVENT_NAME" => $event,
            "LID" => "s1",
            "C_FIELDS" => $fields
        ];
        if($message_id!="")
            $params['MESSAGE_ID'] = (int)$message_id;
        $result = Event::send($params);

        $log = date('Y-m-d H:i:s') . ' ' . print_r([$params ,  $result->getId()], true);
        file_put_contents(__DIR__ . '/EmailNotifications.log', $log . PHP_EOL, FILE_APPEND);

        return $result->getId()>0;
    }
    public static function sentCommonEmail($email, $topic, $text){
        return self::sendEmail('COMMON_MESSAGE', $email, ['TOPIC' => $topic, 'TEXT' => $text]);
    }
    
    public static function sendInfoAboutFreePlaces($user_id, $course_name, $course_link)
    {
        $user = User::find($user_id, ['ID', 'NAME', 'LAST_NAME', 'EMAIL']);
        $mail_params = [
            'NAME' => $user['NAME'],
            'LAST_NAME' => $user['LAST_NAME'],
            'COURSE_NAME' => $course_name,
            'COURSE_LINK' => $course_link,
        ];
        return self::sendEmail('FREE_PLACES_NOTIFY', $user['EMAIL'], $mail_params);
    }

    public static function sendBroadCastLink($user_id, $shedule_id)
    {
        $course = \Teaching\Courses::getByScheduleId($shedule_id);
        $schedule = \Teaching\SheduleCourses::getById($shedule_id);
        if(check_full_array($course)&&check_full_array($schedule)){
            $schedule = current($schedule);
            $user = User::find($user_id);
            $mail_params = [
                'FULL_NAME' => $user['NAME'].' '.$user['LAST_NAME'],
                'TIME' => $schedule['PROPERTIES']['BEGIN_DATE'],
                'COURSE_NAME' => $course['NAME'],
                'LINK' => $schedule['PROPERTIES']['ZOOM_LINK']??$schedule['PROPERTIES']['TG_LINK'],
            ];
            return self::sendEmail('BROADCAST_LINK', $user['EMAIL'], $mail_params);
        }
    }

    public static function send($event, $mail, $params)
    {
        return self::sendEmail($event, $mail, $params);
    }

    public static function sendInfoToTeachingAdminAboutFreePlaces(mixed $ID, string $text_message)
    {
        $user = User::find($ID, ['ID', 'EMAIL', 'NAME', 'LAST_NAME']);
        if(!empty($user['EMAIL'])){
            $mail_params = [
                'NAME' => $user['NAME'],
                'LAST_NAME' => $user['LAST_NAME'],
                'COURSE_LIST' => $text_message,
            ];
            return self::sendEmail('SEND_FREE_PLACES_TO_TEACHING_ADMIN', $user['EMAIL'], $mail_params);
        }

    }

    private static function sendCommonEmail($email, $topic, $text){

        $headers = 'From: Geely <'.\COption::GetOptionString('main', 'email_from').'>' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        //$headers .= 'Content-Type: text/plain; charset=utf-8' . "\r\n";;
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        return mail($email, $topic, $text, $headers);
    }

    /**
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function sendToEmployee($user_id){
        $user = User::find($user_id, ['LOGIN', 'EMAIL', 'UF_DEALER', 'UF_LOCAL_ADMIN']);
        if(($user['UF_LOCAL_ADMIN']==0&&(int)$user['UF_DEALER']>0)) {
            $adminDC = User::getDCAdminByUser($user);
            if(check_full_array($adminDC)) {
                $mail_params = [
                    'LOGIN' => $user['LOGIN'],
                    'PASSWORD' => \Helpers\UserHelper::generatePasswordForUser($user_id),
                ];
                if ($adminDC['ID'] > 0) {
                    $mail_params['ADMIN_NAME'] = $adminDC['NAME'] . ' ' . $adminDC['LAST_NAME'];
                    $mail_params['ADMIN_EMAIL'] = $adminDC['EMAIL'];
                }
                return self::sendEmail('TO_EMPLOYEE', $user['EMAIL'], $mail_params);
            }else{
                $mail_params = [
                    'LOGIN' => $user['LOGIN'],
                    'PASSWORD' => \Helpers\UserHelper::generatePasswordForUser($user_id),
                ];
                return self::sendEmail('TO_EMPLOYEE', $user['EMAIL'], $mail_params);
            }
        }else{
            return false;
        }
    }
    public static function sendToAdminDC($employee_id){
        $user = User::find($employee_id, ['ID', 'LOGIN', 'EMAIL', 'UF_DEALER', 'UF_LOCAL_ADMIN']);
        $mail_params = [
            'LOGIN' => $user['LOGIN'],
            'PASSWORD' => \Helpers\UserHelper::generatePasswordForUser($user['ID']),
        ];
        return self::sendEmail('TO_DC_ADMIN', $user['EMAIL'], $mail_params);
    }
}