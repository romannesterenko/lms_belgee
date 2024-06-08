<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;

$show_mode = true;

$users = \Models\User::getAll();
foreach ($users as $user){
    $list = (new \Teaching\Recruitment())->get(['UF_USER' => $user['ID'], 'UF_DELETED' => false], ['*'], ['UF_TIME' => 'ASC']);
    $last_type = 27;
    foreach ($list as $item) {
        if ($last_type != $item['UF_TYPE']) {
            $last_type = $item['UF_TYPE'];
        } else {
            if(!$show_mode)
                (new \Teaching\Recruitment())->setDeleted($item['ID']);
        }
    }
    if($last_type==27){
        if(check_full_array($list)) {
            $last = end($list);
            if($user['UF_DEALER']==$last['UF_DEALER']) {
                dump($last['ID']);
                dump('Удаление последнее но юзер в дилере');
            }
            if(!$show_mode)
                (new \Teaching\Recruitment())->setDeleted($last['ID']);
        }
    }
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");