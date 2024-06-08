<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;
foreach(\Models\User::getByPartEmail('gtest') as $user){
    if((int)$user['UF_DEALER']>0) {
        dump($user);
        //\Models\User::resetDealer($user['ID']);
    }
}


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");