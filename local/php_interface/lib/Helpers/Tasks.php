<?php

namespace Helpers;
use danog\MadelineProto\Logger;
use danog\MadelineProto\Settings;
use Settings\Common;
use Telegram\ChatTgUsers;
use Telegram\TelegramHandler as Tg;

class Tasks
{
    public static function init(){
        return \Helpers\HLBlockHelper::initialize('task_manager');
    }
    public static function setSendMessageTask($user, $message){
        $tasks = self::init();
        $result = $tasks::add([
            'UF_USER_ID' => $user,
            'UF_ACTION' => 28,
            'UF_COMPLETED' => 24,
            'UF_TEXT_MESSAGE' => $message,
            'UF_CREATED_AT' => date('d.m.Y H:i:s'),
        ]);
    }

    //#####################################


    public static function setRemovedUserAction($ID, $removed_roles)
    {
        if(count($removed_roles)>0){
            foreach ($removed_roles as $role_id){
                $role = current(\Teaching\Roles::getRolesList(['ID' => $role_id], ['ID', 'NAME', 'PROPERTY_TG_CHANEL']));
                $user = \Models\User::find($ID, ['ID', 'UF_TELEGRAM']);
                $user_name = str_replace('@', '', $user['UF_TELEGRAM']);
                if($role['PROPERTY_TG_CHANEL_VALUE']&&$user_name) {
                    self::create(['UF_COMPLETED' => 24, 'UF_ACTION'=>25, 'UF_USER_ID' => $user_name, 'UF_CHAT'=>$role['PROPERTY_TG_CHANEL_VALUE']]);
                }
            }
        }

    }

    public static function setRemoveUserFromTGChannelTask($user, $chat){

        $exists_task = self::get(['UF_COMPLETED' => 24, 'UF_ACTION'=>25, 'UF_USER_ID' => $user, 'UF_CHAT'=>$chat]);
        if(!check_full_array($exists_task)) {
            \Helpers\Log::add(42, 41, 2, 999999, [$user], [$chat]);
            $exists_user = current((new ChatTgUsers())->getArray(['UF_CHAT_ID' => $chat, 'UF_USER_ID' => $user]));
            if (check_full_array($exists_user))
                (new ChatTgUsers())->setBanned($exists_user['ID']);
            self::create(['UF_COMPLETED' => 24, 'UF_ACTION' => 25, 'UF_USER_ID' => $user, 'UF_CHAT' => $chat]);
        }
    }

    public static function setAddUserToTGChannelTask($user, $chat){
        $exists_task = self::get(['UF_ACTION' => 22, 'UF_USER_ID' => $user, 'UF_CHAT' => $chat]);
        if(!check_full_array($exists_task)) {
            \Helpers\Log::add(42, 40, 2, 999999, [$user], [$chat]);
            $log_c = str_replace('-100', '', $chat);
            $exists_user = current((new ChatTgUsers())->getArray(['UF_CHAT_ID' => $log_c, 'UF_USER_ID' => $user]));
            if (check_full_array($exists_user))
                (new ChatTgUsers())->setUser($exists_user['ID']);
            self::create(['UF_COMPLETED' => 24, 'UF_ACTION' => 22, 'UF_USER_ID' => $user, 'UF_CHAT' => $chat]);
        }
    }

    private static function create(array $array)
    {
        /**
        * todo На время теста не ставим таски по другим каналам
        **/
        //if($array['UF_CHAT']=='https://t.me/+nhKbqO4oCt5jNTcy') {
            $exists = self::get($array);
            if(count($exists)==0) {
                $array['UF_CREATED_AT'] = date('d.m.Y H:i:s');
                $tasks = self::init();
                $result = $tasks::add($array);
            }
        //}
    }

    public static function checkExpiredEnrollments(){
        $enrollments = new \Teaching\Enrollments();
        foreach ($enrollments->getExpired() as $enroll){
            $user_id = $enroll['UF_USER_ID'];
            $enrollments->delete($enroll['ID'], false, true);
            /*if($user_id>0){
                $text = 'Ваша заявка на прохождение была просрочена и удалена';
                Notification::sendToUser($user_id, $text);
            }*/
        }
        return '\Helpers\Tasks::checkExpiredEnrollments();';
    }

    public static function checkTgLoop(){
        $settings = new Settings;
        $settings->getLogger()->setLevel(Logger::LEVEL_ULTRA_VERBOSE);
        $session_path = $_SERVER["DOCUMENT_ROOT"] . \Settings\Common::get('telegram_session_path');
        Tg::startAndLoop($session_path, $settings);
        return '\Helpers\Tasks::checkTgLoop();';
    }

    public static function getUncompletedTasks()
    {
        return self::get(['UF_COMPLETED' => 24]);
    }

    public static function getUncompletedRemovedTasks()
    {
        return self::get(['UF_COMPLETED' => 24, "UF_ACTION" => 25]);
    }

    public static function getLastCompletedTaskByUserAndChat($user, $chat)
    {
        return current(self::get(['UF_COMPLETED' => 23, 'UF_USER_ID' => $user, 'UF_CHAT' => $chat]));
    }

    public static function getLastUncompletedTask()
    {
        return current(self::get(['UF_COMPLETED' => 24]));
    }
    public static function processCompletedTasks(){

        foreach (self::getUncompletedTasks() as $task){
            self::completeTask($task['ID']);
            break;
            if($task['UF_ACTION']==22){
                try {
                    $tg = new \Telegram\Telegram();
                    $tg->inviteUserToChat($task['UF_CHAT'], $task['UF_USER_ID']);

                }catch (\Exception $exception){
                    echo $exception->getMessage();
                }
            }

            if($task['UF_ACTION']==25){
                self::completeTask($task['ID']);
                try {
                    $tg = new \Telegram\Telegram();
                    $tg->deleteUserFromChat($task['UF_CHAT'], $task['UF_USER_ID']);
                }catch (\Exception $exception){

                }
            }
            break;
        }
        return '\Helpers\Tasks::processCompletedTasks();';
    }

    public static function processChatUsers(){
        $roles = \Teaching\Roles::getRolesWithTG();
        foreach ($roles as $link => $groupRoles){
            $role_ids = [];
            foreach ($groupRoles as $role) {
                $role_ids[] = $role['ID'];
            }
            $users_tgs = [];
            foreach(\Models\User::getEmployeesByRolesWithTG($role_ids) as $user)
                $users_tgs[] = $user['UF_TELEGRAM'];
            $telegram = new \Telegram\Telegram();
            $telegram->checkUsersFromChat($link, $users_tgs);

        }
        return '\Helpers\Tasks::processChatUsers();';
    }
    public static function setAddedUserAction($ID, array $added_roles)
    {
        if(count($added_roles)>0){
            foreach ($added_roles as $role_id){
                $role = current(\Teaching\Roles::getRolesList(['ID' => $role_id], ['ID', 'NAME', 'PROPERTY_TG_CHANEL']));
                $user = \Models\User::find($ID, ['ID', 'UF_TELEGRAM']);
                $user_name = str_replace('@', '', $user['UF_TELEGRAM']);
                if($role['PROPERTY_TG_CHANEL_VALUE']&&$user_name) {
                    self::create(['UF_COMPLETED' => 24, 'UF_ACTION'=>22, 'UF_USER_ID' => $user_name, 'UF_CHAT'=>$role['PROPERTY_TG_CHANEL_VALUE']]);
                }

            }
        }
    }

    private static function get($filter = [], $select = ['*'], $order = ["ID" => "DESC"])
    {
        $tasks = self::init();
        return $tasks::getList(array(
            "select" => $select,
            "order" => $order,
            "filter" => $filter
        ))->fetchAll();
    }

    public static function completeTask($ID)
    {
        $tasks = self::init();
        $tasks::update($ID, ['UF_COMPLETED'=>23, 'UF_COMPLETED_AT' => date('d.m.Y H:i:s')]);
    }

    public static function setUnbannedUserToTGChannelTask($user, $chat)
    {
        $exists_task = self::get(['UF_COMPLETED' => 24, 'UF_ACTION' => 43, 'UF_USER_ID' => $user, 'UF_CHAT' => $chat]);
        if(!check_full_array($exists_task)) {
            \Helpers\Log::add(42, 40, 2, 999999, [$user], [$chat]);
            $log_c = str_replace('-100', '', $chat);
            $exists_user = current((new ChatTgUsers())->getArray(['UF_CHAT_ID' => $log_c, 'UF_USER_ID' => $user]));
            if (check_full_array($exists_user))
                (new ChatTgUsers())->setUser($exists_user['ID']);
            self::create(['UF_COMPLETED' => 24, 'UF_ACTION' => 43, 'UF_USER_ID' => $user, 'UF_CHAT' => $chat]);
        }
    }

    public static function delete(mixed $ID)
    {
        $tasks = self::init();
        $result = $tasks::delete($ID);
    }

    public static function setRemainTask(mixed $item)
    {
        $settings_hours_period = (int)Common::get('hours_to_remain_free_places')+1;
        $fields = [
            "UF_SHEDULE_ID" => $item['UF_SHEDULE_ID'],
            "UF_COURSE_ID" => $item['UF_COURSE_ID'],
            "UF_TIME" => date('d.m.Y H:i:s', strtotime('+'.$settings_hours_period.' hours'))
        ];
        RemainTasks::create($fields);
    }

    public function processOne($id) {
        $task = $this->get(['ID' => $id]);
        if($task[0]['UF_ACTION']==22){
            try {
                $tg = new \Telegram\Telegram();
                $tg->createChat('Тестовый чатик', \Models\Employee::getByDealer(\Helpers\DealerHelper::getTestDealerId()));
                $tg->completeTask($task[0]['ID']);
            } catch (\Exception $exception){

            }

        }
    }

}