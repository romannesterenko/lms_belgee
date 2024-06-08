<?php
$_SERVER["DOCUMENT_ROOT"] = "/home/u520251/lms.geely-motors.com/www";
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$session_file = $_SERVER["DOCUMENT_ROOT"].'/local/php_interface/madeline_new_session/session.madeline_production';
$MadelineProto = new \danog\MadelineProto\API($session_file);

$_SERVER["HTTP_HOST"] = $_SERVER["DOCUMENT_ROOT"];
$tg_links = new \Telegram\ChatLinks();
foreach ($tg_links->getAll() as $channel){
    switch ($channel['UF_TYPE']){
        case 'supergroup':
            try {
                $tg_users = $MadelineProto->getPwrChat('-100' . $channel['UF_CHAT_ID']);
                if(check_full_array($tg_users)){
                    if(check_full_array($tg_users['participants'])){
                        $system_u = new Telegram\ChatTgUsers();
                        foreach ($tg_users['participants'] as $tg_user) {
                            if ($tg_user['role']=='banned'&&!empty($tg_user['user']['username'])){
                                try {
                                    $req = $MadelineProto->channels->editBanned([
                                            'channel' => '-100'.$channel['UF_CHAT_ID'],
                                            'participant' => $tg_user['user']['username'],
                                            'banned_rights' => [
                                                '_' => 'chatBannedRights',
                                                'view_messages' => false,
                                                'send_messages' => false,
                                                'send_media' => false,
                                                'send_stickers' => true,
                                                'send_gifs' => true,
                                                'send_games' => true,
                                                'send_inline' => true,
                                                'embed_links' => true,
                                                'send_polls' => true,
                                                'change_info' => true,
                                                'invite_users' => true,
                                                'pin_messages' => true,
                                                'until_date' => 62208000
                                            ]
                                    ]);
                                    AddMessage2Log($req);
                                } catch (Exception $e){
                                    AddMessage2Log("Разбан пользователя " . $tg_user['user']['username'] . " в чат ".$channel['UF_CHAT_ID']." не удалось . Причина - " . $e->getMessage());
                                }
                            }
                        }
                    }
                }
            } catch (Exception $e){
                AddMessage2Log($e->getMessage());
            }

            break;
        case 'channel':
                try {
                    $tg_users = $MadelineProto->getPwrChat('-100' . $channel['UF_CHAT_ID']);
                    //AddMessage2Log($tg_users);
                    if(check_full_array($tg_users)){
                        if(check_full_array($tg_users['participants'])){
                            $system_u = new Telegram\ChatTgUsers();
                            foreach ($tg_users['participants'] as $tg_user) {
                                if ($tg_user['role']=='banned'&&!empty($tg_user['user']['username'])){
                                    try {
                                        $req = $MadelineProto->channels->editBanned(
                                            [
                                                'channel' => '-100'.$channel['UF_CHAT_ID'],
                                                'participant' => $tg_user['user']['username'],
                                                'banned_rights' => [
                                                    '_' => 'chatBannedRights',
                                                    'view_messages' => false,
                                                    'send_messages' => false,
                                                    'send_media' => false,
                                                    'send_stickers' => true,
                                                    'send_gifs' => true,
                                                    'send_games' => true,
                                                    'send_inline' => true,
                                                    'embed_links' => true,
                                                    'send_polls' => true,
                                                    'change_info' => true,
                                                    'invite_users' => true,
                                                    'pin_messages' => true,
                                                    'until_date' => 62208000
                                                ]
                                            ]);
                                        AddMessage2Log($req);
                                    } catch (Exception $e){
                                        AddMessage2Log("Разбан пользователя " . $tg_user['user']['username'] . " в чат ".$channel['UF_CHAT_ID']." не удалось . Причина - " . $e->getMessage());
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $e){
                    AddMessage2Log($e->getMessage());
                }
            break;
    }
}
?>
