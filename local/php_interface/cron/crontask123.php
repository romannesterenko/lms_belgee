<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../..");
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php");
$session_file = $_SERVER["DOCUMENT_ROOT"].'/local/php_interface/madeline_new_session/session.madeline_production';
require_once "Database.php";
$MadelineProto = new \danog\MadelineProto\API($session_file);
(new Database())->deleteAllUsers();
function check_full_array($arr){
    return is_array($arr)&&count($arr)>0;
}

//(new Database())->addTelegramUser('qwerty', 'qwerty', 'qwerty', 'qwerty', 'qwerty', "'".date('Y/m/d H:i:s')."'");
foreach ((new Database())->getAllChats() as $allChat) {
    //break;
    if($allChat['UF_TYPE']!='unknown'){
        try {
            sleep(10);
            $tg_users = $MadelineProto->getPwrChat('-100' . $allChat['UF_CHAT_ID']);
            if(check_full_array($tg_users)){
                if(check_full_array($tg_users['participants'])){
                    foreach ($tg_users['participants'] as $tg_user) {
                        $fields = [
                            'UF_USER_NAME' => $tg_user['user']['first_name']??'',
                            'UF_USER_LOGIN' => $tg_user['user']['username']??'',
                            'UF_CHAT_ID' => $allChat['UF_CHAT_ID'],
                            'UF_USER_ID' => $tg_user['user']['id'],
                            'UF_ROLE' => $tg_user['role'],

                            'UF_CREATED_AT' => "'".date('Y/m/d H:i:s')."'",
                        ];
                        (new Database())->addTelegramUser(
                            $fields['UF_USER_NAME'],
                            $fields['UF_USER_LOGIN'],
                            $fields['UF_CHAT_ID'],
                            $fields['UF_USER_ID'],
                            $fields['UF_ROLE'],
                            "'".date('Y/m/d H:i:s')."'"
                        );
                    }
                }
            }
            print_r(count($tg_users));

        } catch (Exception $e){
            print_r($e->getMessage());
        }

    }
}


//(new Database())->addChat($chanel['chat_id'] ?? $chanel['channel_id'], $link, $chanel['type'], $chanel['Chat']['title']);


/*$all_links = [];
(new Database())->deleteAllChats();
foreach((new Database())->getRolesWithGroups() as $roleWithGroup){
    $array['ID'] = $roleWithGroup['IBLOCK_ELEMENT_ID'];
    $array['FIELDS'] = unserialize($roleWithGroup['PROPERTY_65']);
    foreach ($array['FIELDS']['VALUE'] as $link){
        if(!in_array($link, $all_links))
            $all_links[] = $link;
    }
}
$MadelineProto = new \danog\MadelineProto\API($session_file);

foreach ($all_links as $link){
    try {
        $chanel = $MadelineProto->getFullInfo($link);
        if (check_full_array($chanel)) {
            (new Database())->addChat($chanel['chat_id'] ?? $chanel['channel_id'], $link, $chanel['type'], $chanel['Chat']['title']);
        }
    } catch (Exception $e) {
        (new Database())->addChat('', $link, 'unknown', 'unknown', $e->getMessage() . ' (' . $e->getCode() . ')');
    }
}

print_r($all_links);*/