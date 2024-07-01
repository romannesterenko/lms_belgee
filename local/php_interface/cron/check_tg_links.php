<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__).'/../../..');
$_SERVER["REMOTE_ADDR"] = $_SERVER["DOCUMENT_ROOT"];
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require $_SERVER["DOCUMENT_ROOT"] .'/local/php_interface/cron/Database.php';
$session_file = $_SERVER["DOCUMENT_ROOT"].'/local/php_interface/madeline_new/session.madeline_production';
$all_links = [];
$MadelineProto = new \danog\MadelineProto\API($session_file);
(new Database())->deleteAllChats();
foreach((new Database())->getRolesWithGroups() as $roleWithGroup){
    $array['ID'] = $roleWithGroup['IBLOCK_ELEMENT_ID'];
    $array['FIELDS'] = unserialize($roleWithGroup['PROPERTY_65']);
    foreach ($array['FIELDS']['VALUE'] as $link){
        if(!in_array($link, $all_links))
            $all_links[] = $link;
    }
}
$all_links = []; 
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
