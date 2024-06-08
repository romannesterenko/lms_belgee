<?php
$_SERVER["DOCUMENT_ROOT"] = "/home/u520251/lms.geely-motors.com/www";
$_SERVER['HTTP_HOST'] = 'lms.geely-motors.com';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$roles = \Models\Role::getDeactivateFlagRoles();
$date = date('d.m.Y H:i:s', strtotime("-3 months"));
$users = \Models\User::get(['!UF_DEALER' => false, 'ACTIVE' => 'Y', 'UF_ROLE' => array_keys($roles), '<LAST_LOGIN' => $date], ['ID', 'NAME', 'LAST_NAME', 'LAST_LOGIN']);
if(check_full_array($users)){
    foreach ($users as $user) {
        \Models\User::update($user['ID'], ['UF_DEALER' => false, 'ACTIVE' => 'N']);
    }
}
