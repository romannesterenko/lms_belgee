<?php

use Bitrix\Main\Localization\Loc;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER, $notifications_main_filter, $my_courses_filter, $needed_courses_filter, $courses_for_role_filter;
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
$roles = \Models\Role::getList(['ACTIVE' => 'Y'], ['ID', 'NAME', 'PROPERTY_TG_CHANEL']);
$groups_roles = [];
foreach ($roles as $r){
    if(count($r['PROPERTY_TG_CHANEL_VALUE'])>0) {
        foreach ($r['PROPERTY_TG_CHANEL_VALUE'] as $link__){
            $groups_roles[$link__][] = $r['ID'];
        }
    }
}

$chats = (new \Telegram\ChatLinks())->getAll();
foreach ($chats as $chat){
    //if($chat['UF_CHAT_ID']=='1703848629') {
    if($chat['UF_TYPE']!='unknown'&&check_full_array($groups_roles[$chat['UF_LINK']])){
        $users = \Models\User::getListByRole($groups_roles[$chat['UF_LINK']]);
        $tg_users = (new \Telegram\ChatTgUsers())->getUsersByChat($chat['UF_CHAT_ID']);
        $system_tgs = [];
        foreach ($users as $user) {
            if (empty($user['UF_TELEGRAM']))
                continue;
            $tg = prepareTgLogin($user['UF_TELEGRAM']);
            if($tg)
                $system_tgs[$user['ID']] = $tg;
        }
        foreach($tg_users as $tg_user){
            if($tg_user['UF_ROLE']=='user') {
                if (empty($tg_user['UF_USER_LOGIN'])) {
                    \Helpers\Tasks::setRemoveUserFromTGChannelTask($tg_user['UF_USER_ID'], '-100'.$chat['UF_CHAT_ID']);
                } else {
                    if (in_array(strtolower($tg_user['UF_USER_LOGIN']), $system_tgs)) {
                        dump($chat['UF_NAME'].' ('.$chat['UF_LINK'].'). В чате. Не трогаем ' . $tg_user['UF_USER_LOGIN']);
                    } else {
                        \Helpers\Tasks::setRemoveUserFromTGChannelTask($tg_user['UF_USER_ID'], '-100'.$chat['UF_CHAT_ID']);
                        dump($chat['UF_NAME'].' ('.$chat['UF_LINK'].'). В чате. На удаление ' . $tg_user['UF_USER_LOGIN']);
                    }
                }
            }
            if($tg_user['UF_ROLE']=='banned'){
                if (in_array(strtolower($tg_user['UF_USER_LOGIN']), $system_tgs)) {
                    \Helpers\Tasks::setUnbannedUserToTGChannelTask($tg_user['UF_USER_ID'], '-100'.$chat['UF_CHAT_ID']);
                    dump($chat['UF_NAME'].' ('.$chat['UF_LINK'].'). В бане. Восстановление ' . $tg_user['UF_USER_LOGIN']);
                } else {
                    //\Helpers\Tasks::setRemoveUserFromTGChannelTask($tg_user['UF_USER_ID'], $chat['UF_CHAT_ID']);
                    dump($chat['UF_NAME'].' ('.$chat['UF_LINK'].'). В бане. Не трогаем ' . $tg_user['UF_USER_LOGIN']);
                }
            }
        }
        foreach ($system_tgs as $user_id => $system_tg){
            $exists_user = current((new \Telegram\ChatTgUsers())->getArray(['UF_USER_LOGIN' => $system_tg, 'UF_CHAT_ID' => $chat['UF_CHAT_ID']]));
            if(!check_full_array($exists_user)){
                $user = \Models\User::getTelegramLogin($user_id);
                $tg = prepareTgLogin($user);
                if($tg)
                    \Helpers\Tasks::setAddUserToTGChannelTask($tg, '-100'.$chat['UF_CHAT_ID']);
            }
            dump($chat['UF_NAME'].' ('.$chat['UF_LINK'].'). Нет в чате. Добавляем ' . $system_tg);
        }
    }
}
function prepareTgLogin($user){
    if(empty($user)||is_numeric($user))
        return false;
    $tg = str_replace(" ", false, $user);
    $tg = str_replace("https://t.me/", false, $tg);
    $tg = str_replace("t.me/", false, $tg);
    $tg = str_replace("@", false, $tg);
    $tg = str_replace("&nbsp;", false, $tg);
    $tg = preg_replace('/[^A-Za-z0-9_]/i', false, $tg);
    return is_numeric($tg)?false:strtolower($tg);
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>