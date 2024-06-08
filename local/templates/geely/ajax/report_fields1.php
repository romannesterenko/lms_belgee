<?php

use Bitrix\Main\Application;
use Models\Course;
use Models\Dealer;
use Models\Employee;
use Models\Role;
use Teaching\Courses;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$dealer_filter['!ID'] = [360, 292];
$dealer_filter['ACTIVE'] = 'Y';
if(check_full_array($request['regional_ppo'])) {
    $dealer_filter['PROPERTY_REGIONAL_PPO'] = $request['regional_ppo'];
} elseif (check_full_array($request['regional_op'])) {
    $dealer_filter['PROPERTY_REGIONAL'] = $request['regional_op'];
} elseif (check_full_array($request['regional_marketing'])) {
    $dealer_filter['PROPERTY_REGIONAL_MARKETING'] = $request['regional_marketing'];
}
if(!empty($request['country'])) {
    $dealer_filter['PROPERTY_COUNTRY'] = $request['country'];
}
$dealers = Dealer::getList($dealer_filter);
if(check_full_array($dealers) && (!empty($request['country']) || check_full_array($request['regional_ppo']) || check_full_array($request['regional_op']))) {
    foreach ($dealers as $dealer) {
        $filter['UF_DEALER'][] = $dealer['ID'];
    }
}
if(check_full_array($dealers)){
    foreach ($dealers as $dealer){
        $response['dealers'][] = $dealer;
    }
    unset($dealer);
}
if(check_full_array($request['dealer_codes']))
    $filter['UF_DEALER'] = $request['dealer_codes'];
if(!check_full_array($filter['UF_DEALER']))
    $filter['UF_DEALER'] = [Dealer::getByEmployee()];
if($_REQUEST['deleted_employee']=='on'&&$_REQUEST['registered_employee']!='on'){
    $filter['UF_DEALER'] = false;
}
$response['roles_filter'] = [];
switch ($_REQUEST['direction']) {
    case 'S01':
        $op_roles = Role::getArray(['SECTION_ID' => 2, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($op_roles))
            $response['roles_filter'] = array_values(array_unique(array_merge($response['roles_filter'], array_keys($op_roles))));
        break;
    case 'A01':
        $ppo_roles = Role::getArray(['SECTION_ID' => 3, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($ppo_roles))
            $response['roles_filter'] = array_values(array_unique(array_merge($response['roles_filter'], array_keys($ppo_roles))));
        break;
    case 'M01':
        $response['marketing_roles'] = $marketing_roles = Role::getArray(['SECTION_ID' => 139, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($marketing_roles))
            $response['roles_filter'] = array_values(array_unique(array_merge($response['roles_filter'], array_keys($marketing_roles))));
        break;
}

/*
if($_REQUEST['op_servis_op']=='on') {
    $op_roles = \Models\Role::getArray(['SECTION_ID' => 2, 'INCLUDE_SUBSECTIONS' => 'Y']);
    if(check_full_array($op_roles))
        $response['roles_filter'] = array_values(array_unique(array_merge($response['roles_filter'], array_keys($op_roles))));
}
if($_REQUEST['op_servis_servis']=='on') {
    $ppo_roles = \Models\Role::getArray(['SECTION_ID' => 3, 'INCLUDE_SUBSECTIONS' => 'Y']);
    if(check_full_array($ppo_roles))
        $response['roles_filter'] = array_values(array_unique(array_merge($response['roles_filter'], array_keys($ppo_roles))));
}
if($_REQUEST['op_servis_marketing']=='on') {
    $marketing_roles = \Models\Role::getArray(['SECTION_ID' => 139, 'INCLUDE_SUBSECTIONS' => 'Y']);
    if(check_full_array($marketing_roles))
        $response['roles_filter'] = array_values(array_unique(array_merge($response['roles_filter'], array_keys($marketing_roles))));
}*/
$filter['UF_ROLE'] = $response['roles_filter'];
//TODO Убрать его позже
/*if($_REQUEST['op_servis_op']=='on' || $_REQUEST['op_servis_servis']=='on'){
    if($_REQUEST['op_servis_op']=='on' && $_REQUEST['op_servis_servis']!='on'){
        $roles = \Models\Role::getArray(['SECTION_ID' => 2, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($roles))
            $filter['UF_ROLE'] = array_keys($roles);
    }
    if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']=='on'){
        $roles = \Models\Role::getArray(['SECTION_ID' => 3, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($roles))
            $filter['UF_ROLE'] = array_keys($roles);
    }
}*/
$roles_filter = [];
if(check_full_array($filter['UF_ROLE']))
    $roles_filter["ID"] = $filter['UF_ROLE'];


$roles =  check_full_array($roles_filter["ID"])||$_REQUEST['direction'] == 'all'? Role::getList($roles_filter, ['ID', 'NAME']):[];
if(check_full_array($roles)){
    foreach ($roles as $role) {
        $response['roles'][] = $role;
    }
}

if(check_full_array($request['role']))
    $filter['UF_ROLE'] = $request['role'];
//dump($filter);
$users = Employee::getList($filter, ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE', 'UF_DEALER', 'WORK_POSITION']);
if(check_full_array($users)){
    $course_ids = [];
    foreach ($users as $user){
        $course_ids = array_merge($course_ids, Courses::getCoursesByUser($user['ID']));
    }
    if(check_full_array($course_ids)) {
        foreach (Course::getList(['ID' => array_unique($course_ids)], ['ID', 'NAME']) as $c){
            $response['courses'][] = $c;
        }
    }
}

switch ($_REQUEST['direction']) {
    case 'S01':
        unset($response['courses']);
        foreach(Course::getOPList() as $c) {
            $response['courses'][] = $c;
        }
        break;
    case 'A01':
        unset($response['courses']);
        foreach(Course::getPPOList() as $c) {
            $response['courses'][] = $c;
        }
        break;
    case 'M01':
        unset($response['courses']);
        foreach(Course::getMarketingList() as $c) {
            $response['courses'][] = $c;
        }
        break;
}

/*if($_REQUEST['op_servis_op']=='on'||$_REQUEST['op_servis_servis']=='on'){
    if($_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']!='on'){
        unset($response['courses']);
        foreach(\Models\Course::getOPList() as $c){
            $response['courses'][] = $c;
        }
    }
    if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']=='on'){
        unset($response['courses']);
        foreach(\Models\Course::getPPOList() as $c){
            $response['courses'][] = $c;
        }
    }
}*/
$response['filter'] = $filter;

$response['users_getlist'] = $users;
$response['request'] = $request;
echo json_encode($response);


