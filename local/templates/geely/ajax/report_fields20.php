<?php

use Bitrix\Main\Application;
use Helpers\RequestHelper;
use Models\Course;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$dealer_array = [];
$regional_marketing = [];
$regional_ppo = [];
$regional_op = [];
$already_op = [];
$already_ppo = [];
$already_marketing = [];

foreach(\Models\Dealer::getAll(['ID', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL_MARKETING']) as $d) {
    $not_show_ids = [2, 4714];
    if(in_array((int)$d['PROPERTY_REGIONAL_VALUE'], $not_show_ids))
        continue;
    if(in_array((int)$d['PROPERTY_REGIONAL_PPO_VALUE'], $not_show_ids))
        continue;
    if(in_array((int)$d['PROPERTY_REGIONAL_MARKETING_VALUE'], $not_show_ids))
        continue;
    if((int)$d['PROPERTY_REGIONAL_PPO_VALUE']>0) {
        if(!in_array((int)$d['PROPERTY_REGIONAL_PPO_VALUE'], $already_ppo)) {
            $regional_ppo[] = ['ID' => $d['PROPERTY_REGIONAL_PPO_VALUE'], 'NAME' => \Models\User::getFullName($d['PROPERTY_REGIONAL_PPO_VALUE'])];
            $already_ppo[] = (int)$d['PROPERTY_REGIONAL_PPO_VALUE'];
        }
    }
    if((int)$d['PROPERTY_REGIONAL_VALUE']>0) {
        if(!in_array((int)$d['PROPERTY_REGIONAL_VALUE'], $already_op)) {
            $regional_op[] = ['ID' => $d['PROPERTY_REGIONAL_VALUE'], 'NAME' => \Models\User::getFullName($d['PROPERTY_REGIONAL_VALUE'])];
            $already_op[] = (int)$d['PROPERTY_REGIONAL_VALUE'];
        }
    }
    if((int)$d['PROPERTY_REGIONAL_MARKETING_VALUE'] > 0) {
        if(!in_array((int)$d['PROPERTY_REGIONAL_MARKETING_VALUE'], $already_marketing)) {
            $regional_marketing[] = ['ID' => $d['PROPERTY_REGIONAL_MARKETING_VALUE'], 'NAME' => \Models\User::getFullName($d['PROPERTY_REGIONAL_MARKETING_VALUE'])];
            $already_marketing[] = (int)$d['PROPERTY_REGIONAL_MARKETING_VALUE'];
        }
    }
    $dealer_array[] = $d['ID'];
}

switch ($request['direction']) {
    case 'S01':
        $response['regional_op'] = $regional_op;
        break;
    case 'A01':
        $response['regional_op'] = $regional_ppo;
        break;
    case 'M01':
        $response['regional_op'] = $regional_marketing;
        break;
}
if(check_full_array($request['regional_op'])){
    $n1 = [];
    foreach ($request['regional_op'] as $val1){
        $n1[] = (int)$val1;
    }
    $request['regional_op'] = $n1;
    switch ($request['direction']) {
        case 'S01':
            $dealers = \Models\Dealer::getByRegionalOP($request['regional_op']);
            break;
        case 'A01':
            $dealers = \Models\Dealer::getByRegionalPPO($request['regional_op']);
            break;
        case 'M01':
            $dealers = \Models\Dealer::getByRegionalMarketing($request['regional_op']);
            break;
        case 'all':
            $dealers = \Models\Dealer::getList(['ACTIVE' => 'Y']);
    }
} else {
    $dealers = \Models\Dealer::getList(['ACTIVE' => 'Y']);
}
if(check_full_array($dealers)){
    foreach ($dealers as $dealer){
        if(!check_full_array($dealer_array)||(check_full_array($dealer_array)&&in_array($dealer['ID'], $dealer_array)))
            $response['dealers'][] = $dealer;
    }
    unset($dealer);
}

$response['request'] = $request;
echo json_encode($response, JSON_UNESCAPED_UNICODE);


