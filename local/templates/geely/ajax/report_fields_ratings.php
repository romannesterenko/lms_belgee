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
$regional_ppo = [];
$regional_op = [];
$already_op = [];
$already_ppo = [];

    foreach(\Models\Dealer::getAll(['ID', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_PPO']) as $d) {
        $not_show_ids = [2, 4714];
        if(in_array((int)$d['PROPERTY_REGIONAL_VALUE'], $not_show_ids))
            continue;
        if(in_array((int)$d['PROPERTY_REGIONAL_PPO_VALUE'], $not_show_ids))
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
        $dealer_array[] = $d['ID'];
    }

$response['regional_op'] = $regional_op;
$response['regional_ppo'] = $regional_ppo;
if(check_full_array($request['regional_ppo'])){
    $n = [];
    foreach ($request['regional_ppo'] as $val){
        $n[] = (int)$val;
    }
    $request['regional_ppo'] = $n;
    $dealers = \Models\Dealer::getByRegionalPPO($request['regional_ppo']);

}
if(check_full_array($request['regional_op'])){
    $n1 = [];
    foreach ($request['regional_op'] as $val1){
        $n1[] = (int)$val1;
    }
    $request['regional_op'] = $n1;
    $dealers = \Models\Dealer::getByRegionalOP($request['regional_op']);
}
if(!check_full_array($request['regional_op']) && !check_full_array($request['regional_ppo'])) {
    $dealers = \Models\Dealer::getList(['ACTIVE' => 'Y']);
}
if(check_full_array($dealers)){
    foreach ($dealers as $dealer){
        if(!check_full_array($dealer_array)||(check_full_array($dealer_array)&&in_array($dealer['ID'], $dealer_array)))
            $response['dealers'][] = $dealer;
    }
    unset($dealer);
}
//dump($_REQUEST);
switch ($_REQUEST['direction']){
    case 'A01':
        $roles = \Models\Role::getPPORoles();
        if(check_full_array($roles))
            $filter['UF_ROLE'] = array_keys($roles);
        unset($response['courses']);

        foreach(Course::getPPOList() as $c){
            $response['courses'][] = $c;
        }
        break;
    case 'S01':
        $roles = \Models\Role::getOPRoles();
        if(check_full_array($roles))
            $filter['UF_ROLE'] = array_keys($roles);
        unset($response['courses']);
        foreach(Course::getOPList() as $c){
            $response['courses'][] = $c;
        }
        break;
    case 'M01':
        $roles = \Models\Role::getMarketingRoles();
        if(check_full_array($roles))
            $filter['UF_ROLE'] = array_keys($roles);
        unset($response['courses']);
        foreach(Course::getMarketingList() as $c){
            $response['courses'][] = $c;
        }
        break;
    default:
        unset($response['courses']);
        foreach(Course::getAll(['ID', 'NAME']) as $c){
            $response['courses'][] = $c;
        }
}

if((int)$request['course_format'] > 0) {
    $format_courses = Course::getList(['PROPERTY_COURSE_FORMAT' => (int)$request['course_format']], ['ID']);
    foreach ($response['courses'] as $key => $response_course){
        if(!in_array($response_course['ID'], array_keys($format_courses)))
            unset($response['courses'][$key]);
    }
    $response['courses'] = array_values($response['courses']);
}

$roles = \Models\Role::getList(['ID' => $filter['UF_ROLE']], ['ID', 'NAME']);
if(check_full_array($roles)){
    foreach ($roles as $role) {
        $response['roles'][] = $role;
    }
}
$response['request'] = $request;
echo json_encode($response, JSON_UNESCAPED_UNICODE);


