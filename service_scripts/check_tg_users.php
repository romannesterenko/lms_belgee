<?php
$_SERVER["DOCUMENT_ROOT"] = "/home/u520251/lms.geely-motors.com/www";
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$session_file = $_SERVER["DOCUMENT_ROOT"].'/local/php_interface/madeline_new_session/session.madeline_production';
$roles = \Models\Role::getList(['ACTIVE' => 'Y'], ['ID', 'NAME', 'PROPERTY_TG_CHANEL']);
$MadelineProto = new \danog\MadelineProto\API($session_file);

$_SERVER["HTTP_HOST"] = $_SERVER["DOCUMENT_ROOT"];
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
$hlbl = 15;
$hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch();
$entity = HL\HighloadBlockTable::compileEntity($hlblock);
$entity_data_class = $entity->getDataClass();
$result = $entity_data_class::getList(['filter'=>[], 'select' => ['*']]);
$ids = [];
while ($res = $result->fetch()){
    try {
        $entity_data_class::delete($res['ID']);
    } catch (\Exception $e){
        //dump('ID-'.$res['ID'].' - '.$e->getMessage());
    }

}


$tg_links = new \Telegram\ChatLinks();
foreach ($tg_links->getAll() as $channel){
    switch ($channel['UF_TYPE']){
        /*case 'chat':
            try{
                $tg_users = $MadelineProto->messages->getFullChat(['chat_id' => $channel['UF_CHAT_ID']]);
                if(check_full_array($tg_users)){
                    AddMessage2Log($tg_users);
                    if(check_full_array($tg_users['users'])){
                        $system_u = new Telegram\ChatTgUsers();
                        $admin_id = 0;
                        foreach ($tg_users['full_chat']['participants']['participants'] as $participant) {
                            if ($participant['_'] == 'chatParticipantCreator') {
                                $admin_id = $participant['user_id'];
                            }
                        }
                        foreach ($tg_users['users'] as $tg_user) {
                            if(!$system_u->isExistsUser($tg_user['id'], $channel['UF_CHAT_ID'])) {
                                $fields = [
                                    'UF_USER_NAME' => $tg_user['first_name'],
                                    'UF_USER_LOGIN' => $tg_user['username'],
                                    'UF_CHAT_ID' => $channel['UF_CHAT_ID'],
                                    'UF_USER_ID' => $tg_user['id'],
                                    'UF_ROLE' => $tg_user['id']==$admin_id?'creator':'user',
                                    'UF_CREATED_AT' => date('d.m.Y H:i:s'),
                                ];
                                $system_u->addUser($fields);
                            }
                        }
                    }
                }
            } catch (Exception $e){
                echo $e->getMessage();
            }
            break;*/
        case 'supergroup':
            try {
                $tg_users = $MadelineProto->getPwrChat('-100' . $channel['UF_CHAT_ID']);
                //AddMessage2Log($tg_users);
                if(check_full_array($tg_users)){
                    if(check_full_array($tg_users['participants'])){
                        $system_u = new Telegram\ChatTgUsers();
                        foreach ($tg_users['participants'] as $tg_user) {
                            if(!$system_u->isExistsUser($tg_user['user']['id'], $channel['UF_CHAT_ID'])) {
                                $fields = [
                                    'UF_USER_NAME' => $tg_user['user']['first_name']??'',
                                    'UF_USER_LOGIN' => $tg_user['user']['username']??'',
                                    'UF_CHAT_ID' => $channel['UF_CHAT_ID'],
                                    'UF_USER_ID' => $tg_user['user']['id'],
                                    'UF_ROLE' => $tg_user['role'],

                                    'UF_CREATED_AT' => date('d.m.Y H:i:s'),
                                ];
                                $system_u->addUser($fields);
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
                                if(!$system_u->isExistsUser($tg_user['user']['id'], $channel['UF_CHAT_ID'])) {
                                    $fields = [
                                        'UF_USER_NAME' => $tg_user['user']['first_name']??'',
                                        'UF_USER_LOGIN' => $tg_user['user']['username']??'',
                                        'UF_CHAT_ID' => $channel['UF_CHAT_ID'],
                                        'UF_USER_ID' => $tg_user['user']['id'],
                                        'UF_ROLE' => $tg_user['role'],

                                        'UF_CREATED_AT' => date('d.m.Y H:i:s'),
                                    ];
                                    $system_u->addUser($fields);
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
