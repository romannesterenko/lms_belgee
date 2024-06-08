<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../..");
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php");
$session_file = $_SERVER["DOCUMENT_ROOT"].'/local/php_interface/madeline_new_session/session.madeline_production';
$_SERVER["HTTP_HOST"] = $_SERVER["DOCUMENT_ROOT"];
require_once "Database.php";
die();
$db = new Database();
$today_completed_tasks = $db->getTodayCompletedTasks();
if(count($today_completed_tasks)>=$db->getLimitInvites())
    die();
$tasks = $db->getUncompletedTasks(1);
sleep(rand(60, 300));
function check_full_array($arr):bool{
    return is_array($arr)&&count($arr)>0;
}
function AddMessage2Log($expr):void {
    $log = $_SERVER["DOCUMENT_ROOT"].'/file.txt';
    $string = 'Дата записи: '.date('d.m.Y H:i:s').PHP_EOL.PHP_EOL;
    file_put_contents($log, $string.print_r($expr, 1)."\n", FILE_APPEND);
}
//$tasks = [];
if( is_array($tasks) && count($tasks) > 0 ){
    $MadelineProto = new \danog\MadelineProto\API($session_file);
    foreach ($tasks as $task){

        if($task['UF_ACTION'] == 28) {
            if ( !empty($task['UF_USER_ID']) && $task['UF_USER_ID'] != '' ) {
                try {
                    $MadelineProto->messages->sendMessage(['peer' => '@' . $task['UF_USER_ID'], 'message' => iconv('windows-1251', 'utf-8', $task['UF_TEXT_MESSAGE'])]);
                    $db->setCompleteTask($task['ID']);
                } catch (Exception $e) {
                    $db->setCompleteTask($task['ID'], $e->getMessage().' ('.$e->getCode().')');
                }
            }
        }
        if($task['UF_ACTION'] == 22) {
            if ( !empty($task['UF_USER_ID']) && $task['UF_USER_ID'] != '' ) {
                $chat_id = str_replace('-100', false, $task['UF_CHAT']);
                $chat_info = $db->getChatByID($chat_id);
                $chat['type']=$chat_info['UF_TYPE'];
                $chat['Chat']['title'] = $chat_info['UF_NAME'];
                $chat['full']['exported_invite']['link'] = $chat_info['UF_LINK'];
                if($chat_info['UF_TYPE']=='channel'||$chat_info['UF_TYPE']=='supergroup')
                    $chat['chat_id'] = $task['UF_CHAT'];
                if(!empty($chat['chat_id'])) {
                    $is_flood = false;
                    if($chat['type']=='channel'||$chat['type']=='supergroup') {
                        try {
                            $message = "Добрый день!\nВ соответствии с Вашей должностью и ролью в Geely LMS, Вы приглашены в Telegram канал '" . $chat['Chat']['title'] . "'. Для подключения к перейдите, пожалуйста, по ссылке: ".$chat['full']['exported_invite']['link'];
                            $peer = $task['UF_USER_ID'];
                            try {
                                $MadelineProto->messages->sendMessage(['peer' => '@'.$task['UF_USER_ID'], 'message' => $message]);
                                AddMessage2Log("Отправлено сообщение ".$task['UF_USER_ID'].".\n".$message);
                            } catch (Exception $e) {
                                if($e->getMessage()=='PEER_FLOOD') {
                                    AddMessage2Log("Добавление пользователя ".$task['UF_USER_ID']." не удалось. Причина - ".$e->getMessage());
                                    \Notifications\EmailNotifications::sentCommonEmail('romannesterenko87@gmail.com', 'PEER_FLOOD', "Добавление пользователя ".$task['UF_USER_ID']." не удалось. Причина - ".$e->getMessage());
                                    $is_flood = true;
                                } else {
                                    AddMessage2Log("Добавление пользователя ".$task['UF_USER_ID']." не удалось. Причина - ".$e->getMessage());
                                }
                            }
                        } catch (Exception $e){
                            if($e->getMessage()=='USER_PRIVACY_RESTRICTED'){
                                $message = "Добрый день!\nВ соответствии с Вашей должностью и ролью в Geely LMS, Вы приглашены в информационный Telegram канал '" . $chat['Chat']['title'] . "'. Для подключения к каналу перейдите, пожалуйста, по ссылке: ".$chat['full']['exported_invite']['link'];
                                $peer = $task['UF_USER_ID'];
                                try {
                                    $MadelineProto->messages->sendMessage(['peer' => '@'.$task['UF_USER_ID'], 'message' => $message]);
                                    AddMessage2Log("Отправлено сообщение ".$task['UF_USER_ID'].".\n".$message);
                                } catch (Exception $e) {
                                    AddMessage2Log("Сообщение не отправлено ".$task['UF_USER_ID'].". Причина - ".$e->getMessage());

                                }
                            } elseif($e->getMessage()=='USERS_TOO_MUCH') {
                                $message = "Добрый день!\nВ соответствии с Вашей должностью и ролью в Geely LMS, Вы приглашены в информационный Telegram канал '" . $chat['Chat']['title'] . "'. Для подключения к каналу перейдите, пожалуйста, по ссылке: ".$chat['full']['exported_invite']['link'];
                                $peer = $task['UF_USER_ID'];
                                try {
                                    $MadelineProto->messages->sendMessage(['peer' => '@' . $task['UF_USER_ID'], 'message' => $message]);
                                    AddMessage2Log("Отправлено сообщение ".$task['UF_USER_ID'].".\n".$message);
                                } catch (Exception $e) {
                                    if($e->getMessage()=='PEER_FLOOD') {
                                        AddMessage2Log("Добавление пользователя ".$task['UF_USER_ID']." не удалось. Причина - ".$e->getMessage());
                                        $is_flood = true;
                                    } else {
                                        AddMessage2Log("Добавление пользователя ".$task['UF_USER_ID']." не удалось. Причина - ".$e->getMessage());
                                    }
                                }
                            } elseif($e->getMessage()=='PEER_FLOOD') {
                                AddMessage2Log("Добавление пользователя ".$task['UF_USER_ID']." не удалось. Причина - ".$e->getMessage());
                                \Notifications\EmailNotifications::sentCommonEmail('romannesterenko87@gmail.com', 'PEER_FLOOD', "Добавление пользователя ".$task['UF_USER_ID']." не удалось. Причина - ".$e->getMessage());
                                $is_flood = true;
                            } else {
                                AddMessage2Log("Добавление пользователя ".$task['UF_USER_ID']." не удалось. Причина - ".$e->getMessage());
                            }
                        }
                    } else {
                        $MadelineProto->messages->addChatUser(['chat_id' => $chat['chat_id'], 'user_id' => $task['UF_USER_ID']]);
                    }
                    if(!$is_flood)
                        $db->setCompleteTask($task['ID']);
                    die();
                }
            }
        }
        if($task['UF_ACTION'] == 43) {
            if ( !empty($task['UF_USER_ID']) && $task['UF_USER_ID'] != '' ) {
                $user = $db->getUserLogin($task['UF_USER_ID']);
                //AddMessage2Log($user);
                $chat_id = str_replace('-100', false, $task['UF_CHAT']);
                $chat_info = $db->getChatByID($chat_id);
                //$user = current((new \Telegram\ChatTgUsers)->getArray(['UF_USER_ID' => $task['UF_USER_ID']]));

                $chat['type']=$chat_info['UF_TYPE'];
                $chat['Chat']['title'] = $chat_info['UF_NAME'];
                $chat['full']['exported_invite']['link'] = $chat_info['UF_LINK'];
                if($chat_info['UF_TYPE']=='channel'||$chat_info['UF_TYPE']=='supergroup')
                    $chat['chat_id'] = $task['UF_CHAT'];
                if(!empty($chat['chat_id'])) {
                    $is_flood = false;
                    if($chat['type']=='channel'||$chat['type']=='supergroup') {
                        try {
                            $MadelineProto->channels->editBanned(
                                [
                                    'channel' => $task['UF_CHAT'],
                                    'participant' => '@'.$user['UF_USER_LOGIN'],
                                    'banned_rights' => ['_' => 'chatBannedRights', 'view_messages' => false, 'send_messages' => false, 'send_media' => false, 'send_stickers' => true, 'send_gifs' => true, 'send_games' => true, 'send_inline' => true, 'embed_links' => true, 'send_polls' => true, 'change_info' => true, 'invite_users' => true, 'pin_messages' => true, 'until_date' => 0]
                                ]);
                            AddMessage2Log("разбан " . $task['UF_USER_ID'] . " - " . $task['UF_CHAT']);
                            if(check_full_array($user) && !empty($user['UF_USER_LOGIN'])) {
                                try {
                                    $message = "Добрый день!\nВ соответствии с Вашей должностью и ролью в Geely LMS, Вы приглашены в Telegram канал '" . $chat['Chat']['title'] . "'. Для подключения к перейдите, пожалуйста, по ссылке: " . $chat['full']['exported_invite']['link'];
                                    $peer = $task['UF_USER_ID'];

                                    try {
                                        $MadelineProto->messages->sendMessage(['peer' => '@' . $user['UF_USER_LOGIN'], 'message' => $message]);
                                        AddMessage2Log("Отправлено сообщение " . $task['UF_USER_ID'] . ".\n" . $message);
                                    } catch (Exception $e) {
                                        if ($e->getMessage() == 'PEER_FLOOD') {
                                            AddMessage2Log("Добавление пользователя " . $task['UF_USER_ID'] . " не удалось. Причина - " . $e->getMessage());
                                            \Notifications\EmailNotifications::sentCommonEmail('romannesterenko87@gmail.com', 'PEER_FLOOD', "Добавление пользователя " . $task['UF_USER_ID'] . " не удалось. Причина - " . $e->getMessage());
                                            $is_flood = true;
                                        } else {
                                            AddMessage2Log("Отправка сообщения " . $task['UF_USER_ID'] . " не удалась. Причина - " . $e->getMessage());
                                        }
                                    }

                                } catch (Exception $e) {
                                    if ($e->getMessage() == 'USER_PRIVACY_RESTRICTED') {
                                        $message = "Добрый день!\nВ соответствии с Вашей должностью и ролью в Geely LMS, Вы приглашены в информационный Telegram канал '" . $chat['Chat']['title'] . "'. Для подключения к каналу перейдите, пожалуйста, по ссылке: " . $chat['full']['exported_invite']['link'];
                                        $peer = $task['UF_USER_ID'];
                                        try {
                                            $MadelineProto->messages->sendMessage(['peer' => '@' . $task['UF_USER_ID'], 'message' => $message]);
                                            AddMessage2Log("Отправлено сообщение " . $task['UF_USER_ID'] . ".\n" . $message);
                                        } catch (Exception $e) {
                                            AddMessage2Log("Сообщение не отправлено " . $task['UF_USER_ID'] . ". Причина - " . $e->getMessage());

                                        }
                                    } elseif ($e->getMessage() == 'USERS_TOO_MUCH') {
                                        $message = "Добрый день!\nВ соответствии с Вашей должностью и ролью в Geely LMS, Вы приглашены в информационный Telegram канал '" . $chat['Chat']['title'] . "'. Для подключения к каналу перейдите, пожалуйста, по ссылке: " . $chat['full']['exported_invite']['link'];
                                        $peer = $task['UF_USER_ID'];
                                        try {
                                            $MadelineProto->messages->sendMessage(['peer' => '@' . $task['UF_USER_ID'], 'message' => $message]);
                                            AddMessage2Log("Отправлено сообщение " . $task['UF_USER_ID'] . ".\n" . $message);
                                        } catch (Exception $e) {
                                            if ($e->getMessage() == 'PEER_FLOOD') {
                                                AddMessage2Log("Добавление пользователя " . $task['UF_USER_ID'] . " не удалось. Причина - " . $e->getMessage());
                                                $is_flood = true;
                                            } else {
                                                AddMessage2Log("Добавление пользователя " . $task['UF_USER_ID'] . " не удалось. Причина - " . $e->getMessage());
                                            }
                                        }
                                    } elseif ($e->getMessage() == 'PEER_FLOOD') {
                                        AddMessage2Log("Добавление пользователя " . $task['UF_USER_ID'] . " не удалось. Причина - " . $e->getMessage());
                                        \Notifications\EmailNotifications::sentCommonEmail('romannesterenko87@gmail.com', 'PEER_FLOOD', "Добавление пользователя " . $task['UF_USER_ID'] . " не удалось. Причина - " . $e->getMessage());
                                        $is_flood = true;
                                    } else {
                                        AddMessage2Log("Добавление пользователя " . $task['UF_USER_ID'] . " не удалось. Причина - " . $e->getMessage());
                                    }
                                }
                            }
                        } catch (Exception $e){
                            AddMessage2Log("Добавление пользователя " . $task['UF_USER_ID'] . " не удалось 1. Причина - " . $e->getMessage());
                        }
                    } else {
                        $MadelineProto->messages->addChatUser(['chat_id' => $chat['chat_id'], 'user_id' => $task['UF_USER_ID']]);
                    }
                    if(!$is_flood)
                        $db->setCompleteTask($task['ID']);
                    die();
                }
            }
        }
        if($task['UF_ACTION'] == 25) {
            if ( !empty($task['UF_USER_ID']) && $task['UF_USER_ID'] != '' ) {
                try {
                    $MadelineProto->channels->editBanned(
                        [
                            'channel' => $task['UF_CHAT'],
                            'participant' => $task['UF_USER_ID'],
                            'banned_rights' => ['_' => 'chatBannedRights', 'view_messages' => true, 'send_messages' => true, 'send_media' => true, 'send_stickers' => true, 'send_gifs' => true, 'send_games' => true, 'send_inline' => true, 'embed_links' => true, 'send_polls' => true, 'change_info' => true, 'invite_users' => true, 'pin_messages' => true, 'until_date' => 0]
                        ]);
                    AddMessage2Log("Бан ".$task['UF_USER_ID']." - ".$task['UF_CHAT']);
                    $db->setCompleteTask($task['ID']);
                } catch (Exception $e) {
                    AddMessage2Log("Невозможно забанить ".$task['UF_USER_ID']." - ".$task['UF_CHAT'].". Причина - ".$e->getMessage());
                    $db->setCompleteTask($task['ID']);
                }
            }
        }
    }
}
