<?php

use Bitrix\Main\Application;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$found = false;
$list = [];
$name = trim($request['search']);
$array = ['(', ')', '-', ' ', '+', '-'];
$phone = str_replace($array, '', $request['search']);
$filter = \Bitrix\Main\UserUtils::getUserSearchFilter(Array(
    'FIND' => $name
));
$filter['!UF_DEALER'] = false;
$filter['IS_REAL_USER'] = 'Y';
$res = Bitrix\Main\UserTable::getList(Array(
    "select"=>Array("ID", "NAME", "LAST_NAME", "UF_DEALER", "EMAIL", "PERSONAL_MOBILE"),
    "filter"=>$filter,
));
while ($arRes = $res->fetch()) {
    $list[$arRes['ID']] = $arRes;
}
$res1 = Bitrix\Main\UserTable::getList(Array(
    "select"=>Array("ID", "NAME", "LAST_NAME", "UF_DEALER", "EMAIL", "PERSONAL_MOBILE"),
    "filter"=>['?PERSONAL_MOBILE' => $phone, '!UF_DEALER' => false],
));
while ($arRes1 = $res1->fetch()) {
    if(!check_full_array($list[$arRes1['ID']]))
        $list[$arRes1['ID']] = $arRes1;
}
$res2 = Bitrix\Main\UserTable::getList(Array(
    "select"=>Array("ID", "NAME", "LAST_NAME", "UF_DEALER", "EMAIL", "PERSONAL_MOBILE"),
    "filter"=>['ID' => $phone, '!UF_DEALER' => false],
));
while ($arRes2 = $res2->fetch()) {
    if(!check_full_array($list[$arRes2['ID']]))
        $list[$arRes2['ID']] = $arRes2;
}
$dealers = [];
$dealers_i = [];
if(check_full_array($list)){
    foreach ($list as $user)
        $dealers[] = $user['UF_DEALER'];

    $dealer_array = \Models\Dealer::getList(['ID' => $dealers]);

    foreach ($dealer_array as $dealer){
        $dealers_i[$dealer['ID']] = $dealer;
    }
    $found = true;
}
foreach ($list as &$us){
    $us['DEALER'] = $dealers_i[$us['UF_DEALER']]['NAME']." (".$dealers_i[$us['UF_DEALER']]['CODE'].")";
}

echo json_encode(['filter' => $filter, 'list' => $list, 'found' => $found]);

