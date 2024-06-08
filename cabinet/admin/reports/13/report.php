<?php

use Bitrix\Main\Localization\Loc;
use Models\User;
use Teaching\Courses;
use Teaching\Roles;

const NEED_AUTH=true;
global $USER;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");


\Helpers\Log::write(['user' => $USER->GetID(), 'report' => '13', 'request' => $_REQUEST]);
$completions = new \Teaching\CourseCompletion();
$settings = \Settings\Reports::getByCode('courses_for_dealer');
$dealer_id = (int)$_REQUEST['dealer_id']?(int)$_REQUEST['dealer_id']:\Helpers\UserHelper::getDealerId();
$dealers = \Helpers\DealerHelper::getList(['ACTIVE' => 'Y', '!ID' => 360], ['ID', 'NAME', 'PROPERTY_CITY', 'CODE']);
$table_th = [];
$roles_array = [];
$from_date = false;
$from_to = false;
if(!empty($_REQUEST['from'])) {
    $from_date = date('d.m.Y', strtotime($_REQUEST['from']));
}
if(!empty($_REQUEST['to'])) {
    $from_to = date('d.m.Y', strtotime($_REQUEST['to']));
}
$all_mans['op'] = [];
$all_mans['ppo'] = [];
$all_mans['marketing'] = [];
switch ($_REQUEST['direction']) {
    case 'A01':
        $roles_array = \Models\Role::getArray(['SECTION_ID' => 3, 'INCLUDE_SUBSECTIONS' => 'Y']);
        $roles_array = check_full_array($roles_array)?array_keys($roles_array):[];
        break;
    case 'S01':
        $roles_array = \Models\Role::getArray(['SECTION_ID' => 2, 'INCLUDE_SUBSECTIONS' => 'Y']);
        $roles_array = check_full_array($roles_array)?array_keys($roles_array):[];
        break;
    case 'M01':
        $roles_array = \Models\Role::getArray(['SECTION_ID' => 139, 'INCLUDE_SUBSECTIONS' => 'Y']);
        $roles_array = check_full_array($roles_array)?array_keys($roles_array):[];
        break;
}
if(check_full_array($_REQUEST['role']))
    $roles_array = $_REQUEST['role'];
$need_request = true;
if(check_full_array($roles_array)){
    $all_roles_list = Roles::getRolesList(['ID' => array_unique($roles_array)], ['ID', 'NAME']);
    $all_courses_list = Courses::getByRoleArray(array_keys($all_roles_list), ['ID', 'NAME']);
    //$need_request = false;
}
$completions_array = [];
$period_completions_array = [];
$managers_info = [];
foreach ($dealers as &$dealer_) {
    if($dealer_["ID"]==360)
        continue;
    $roles = [];
    $getListParams['select'] = ['ID', 'UF_ROLE', 'EMAIL'];
    $getListParams['filter'] = ['UF_DEALER' => $dealer_['ID']];
    if(check_full_array($roles_array)) {
        $getListParams['filter']['UF_ROLE'] = $roles_array;
        $roles = $roles_array;
    }
    $dealer_['EMPLOYEES'] = User::getArray($getListParams);
    $dealer_['EMPLOYESS_CNT'] = count($dealer_['EMPLOYEES']);
    $dealer_['REGIONAL_OP'] = \Models\Dealer::getRegionalOP($dealer_['ID']);
    $dealer_['REGIONAL_PPO'] = \Models\Dealer::getRegionalPPO($dealer_['ID']);
    $dealer_['REGIONAL_MARKETING'] = \Models\Dealer::getRegionalMarketing($dealer_['ID']);
    if($dealer_['REGIONAL_OP']&&!in_array($dealer_['REGIONAL_OP'], $all_mans['op'])){
        $all_mans['op'][] = $dealer_['REGIONAL_OP'];
    }
    if($dealer_['REGIONAL_PPO']&&!in_array($dealer_['REGIONAL_PPO'], $all_mans['ppo'])){
        $all_mans['ppo'][] = $dealer_['REGIONAL_PPO'];
    }
    if($dealer_['REGIONAL_MARKETING']&&!in_array($dealer_['REGIONAL_MARKETING'], $all_mans['marketing'])){
        $all_mans['marketing'][] = $dealer_['REGIONAL_PPO'];
    }
    $need_ppo = true;
    $need_op = true;

    if($need_op&&$dealer_['REGIONAL_OP']&&$dealer_['REGIONAL_OP']!=''){

        $managers_info[$dealer_['REGIONAL_OP']]['EMPLOYESS_CNT'] = (int)$managers_info[$dealer_['REGIONAL_OP']]['EMPLOYESS_CNT'];
        $managers_info[$dealer_['REGIONAL_OP']]['EMPLOYESS_CNT'] += $dealer_['EMPLOYESS_CNT'];

    }
    if($need_ppo&&$dealer_['REGIONAL_PPO']&&$dealer_['REGIONAL_PPO']!=''){

        $managers_info[$dealer_['REGIONAL_PPO']]['EMPLOYESS_CNT'] = (int)$managers_info[$dealer_['REGIONAL_PPO']]['EMPLOYESS_CNT'];
        $managers_info[$dealer_['REGIONAL_PPO']]['EMPLOYESS_CNT'] += $dealer_['EMPLOYESS_CNT'];

    }
    if($need_request) {
        $dealer_['ROLES_LIST'] = Roles::getRolesList(['ID' => array_unique($roles)], ['ID', 'NAME']);

        $dealer_['COURSES'] = Courses::getByRoleArray(array_keys($dealer_['ROLES_LIST']), ['ID', 'NAME']);
        switch ($_REQUEST['direction']) {
            case 'A01':
                $dealer_['COURSES'] = Courses::getList(['PROPERTY_ROLES' => array_keys($dealer_['ROLES_LIST']), 'SECTION_ID' => 17, 'INCLUDE_SUBSECTIONS' => 'Y'], ['ID', 'NAME', 'PROPERTY_ROLES']);
                break;
            case 'S01':
                $dealer_['COURSES'] = Courses::getList(['PROPERTY_ROLES' => array_keys($dealer_['ROLES_LIST']), 'SECTION_ID' => 4, 'INCLUDE_SUBSECTIONS' => 'Y'], ['ID', 'NAME', 'PROPERTY_ROLES']);

                break;
            case 'M01':
                $dealer_['COURSES'] = Courses::getList(['PROPERTY_ROLES' => array_keys($dealer_['ROLES_LIST']), 'SECTION_ID' => 138, 'INCLUDE_SUBSECTIONS' => 'Y'], ['ID', 'NAME', 'PROPERTY_ROLES']);
                break;
        }
        $items = [];
        foreach ($dealer_['COURSES'] as $course)
            $items[$course['ID']] = $course;
        $dealer_['COURSES'] = $items;
    } else {
        $dealer_['ROLES_LIST'] = $all_roles_list;
        $dealer_['COURSES'] = $all_courses_list;
    }
    $roles_arr = [];
    foreach ($dealer_['COURSES'] as $c) {
        foreach ($c['PROPERTY_ROLES_VALUE'] as $r) {
            $roles_arr[$r][$c['ID']] = $c;
        }
    }
    foreach ($dealer_['ROLES_LIST'] as $role_id => &$role){
        $role['COURSES'] = $roles_arr[$role['ID']];
        if(check_full_array($role['COURSES'])&&count($role['COURSES'])>0)
            $table_th[$role['ID']] = $role;
        foreach ($dealer_['EMPLOYEES'] as $employee) {
            if (is_array($employee['UF_ROLE'])&&in_array($role['ID'], $employee['UF_ROLE']))
                $role['USERS'][] = $employee;
        }
    }
    $dealer_courses = [];
    foreach ($dealer_['ROLES_LIST'] as $role_id => &$role){
        foreach ($role['COURSES'] as &$role_foreach_course){
            if(!check_full_array($completions_array[$role_foreach_course['ID']])) {
                $completions_filter = $period_completions_filter = ['UF_COURSE_ID' => $role_foreach_course['ID'], 'UF_IS_COMPLETE' => 1];
                if (\Models\Course::isFreeEntrance($role_foreach_course['ID'])){
                    if($from_date)
                        $period_completions_filter['>=UF_COMPLETED_TIME'] = $from_date." 00:00:01";
                    if($from_to)
                        $period_completions_filter['<=UF_COMPLETED_TIME'] = $from_to." 23:59:59";
                } else {
                    if($from_date)
                        $period_completions_filter['>=UF_DATE'] = $from_date." 00:00:01";
                    if($from_to)
                        $period_completions_filter['<=UF_DATE'] = $from_to." 23:59:59";
                }

                $completions = (new \Teaching\CourseCompletion())->get($completions_filter);
                $period_completions = (new \Teaching\CourseCompletion())->get($period_completions_filter);
                foreach ($completions as $completion){
                    $completions_array[$completion['UF_COURSE_ID']][$completion['UF_USER_ID']] = $completion;
                }
                foreach ($period_completions as $period_completion){
                    $period_completions_array[$period_completion['UF_COURSE_ID']][$period_completion['UF_USER_ID']] = $period_completion;
                }
                $complete_items = $completions_array[$role_foreach_course['ID']];
                $period_complete_items = $period_completions_array[$role_foreach_course['ID']];
            } else {
                $complete_items = $completions_array[$role_foreach_course['ID']];
                $period_complete_items = $period_completions_array[$role_foreach_course['ID']];
            }
            if($need_ppo) {
                $managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$role_foreach_course['ID']]['NAME'] = $role_foreach_course['NAME'];
                $managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$role_foreach_course['ID']]['COMPLETED'] = (int)$managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$role_foreach_course['ID']]['COMPLETED'];
                $managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$role_foreach_course['ID']]['PERIOD_COMPLETED'] = (int)$managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$role_foreach_course['ID']]['PERIOD_COMPLETED'];
            }
            if($need_op) {
                $managers_info[$dealer_['REGIONAL_OP']]['COURSES'][$role_foreach_course['ID']]['NAME'] = $role_foreach_course['NAME'];
                $managers_info[$dealer_['REGIONAL_OP']]['COURSES'][$role_foreach_course['ID']]['COMPLETED'] = (int)$managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$role_foreach_course['ID']]['COMPLETED'];
                $managers_info[$dealer_['REGIONAL_OP']]['COURSES'][$role_foreach_course['ID']]['PERIOD_COMPLETED'] = (int)$managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$role_foreach_course['ID']]['PERIOD_COMPLETED'];
            }
            $role_foreach_course['COMPLETED'] = 0;
            $role_foreach_course['PERIOD_COMPLETED'] = 0;
            foreach ($role['USERS'] as $user){
                if(check_full_array($complete_items[$user['ID']])) {
                    $role_foreach_course['COMPLETED']++;
                    if($need_ppo) {
                        $managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$role_foreach_course['ID']]['COMPLETED']++;
                    }
                    if($need_op) {
                        $managers_info[$dealer_['REGIONAL_OP']]['COURSES'][$role_foreach_course['ID']]['COMPLETED']++;
                    }
                }
                if(check_full_array($period_complete_items[$user['ID']])) {
                    $role_foreach_course['PERIOD_COMPLETED']++;
                    if($need_ppo) {
                        $managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$role_foreach_course['ID']]['PERIOD_COMPLETED']++;
                    }
                    if($need_op) {
                        $managers_info[$dealer_['REGIONAL_OP']]['COURSES'][$role_foreach_course['ID']]['PERIOD_COMPLETED']++;
                    }
                }
            }

            $dealer_courses[$role_foreach_course['ID']] = ['NAME' => $role_foreach_course['NAME']];
            $dealer_courses[$role_foreach_course['ID']]['COMPLETED'] = (int)$dealer_courses[$role_foreach_course['ID']]['COMPLETED'];
            $dealer_courses[$role_foreach_course['ID']]['COMPLETED']+=$role_foreach_course['COMPLETED'];
            $role_foreach_course['COMPLETED_PERCENTS'] = $role_foreach_course['COMPLETED']==0?'0%':floor($role_foreach_course['COMPLETED']/count($role['USERS'])*100).'%';
        }
    }

}
$all_manager_courses = [];
foreach ($managers_info as $v){
    if(check_full_array($v['COURSES'])){
        $all_manager_courses = $v['COURSES'];
        break;
    }
}
$_REQUEST['report_id'] = $dealer_id;?>
<div class="main-content">
    <div class="content">
        <div class="content-block">
            <input type="hidden" id="is_adaptive" value="<?=$settings['PROPERTIES']['IS_ADAPTIVE']?>">
            <input type="hidden" id="count" value="10">
            <div class="text-content text-content--long">
                <h2 class="h2 center">Отчет по дилерской сети</h2>
                <div class="form-group" style="display: flex; padding-top: 1rem;">
                    <div class="btn-center">
                        <a href="." class="btn">К генератору</a>
                    </div>
                </div>
                <div class="table-block">
                    <div class="form-group" style="display: flex; padding-top: 1rem;">
                        <div class="btn-center">
                            <button class="btn" id="gen13_1"><span>Excel</span></button>
                        </div>
                    </div>
                    <table class="table table-bordered" id="table-report13_1" style="padding-top: 25px">

                        <thead class="thead-dark">
                            <tr>
                                <th rowspan="2" style="vertical-align: middle;"></th>
                                <th rowspan="2" style="vertical-align: middle;">Всего сотрудников</th>
                                <?php foreach ($all_manager_courses as $crs) {?>
                                    <?php if($from_date||$from_to) {?>
                                        <th colspan="3" class="text-center" style="vertical-align: middle;"><b><?=$crs['NAME']?></b></th>
                                    <?php } else {?>
                                        <th colspan="2" class="text-center" style="vertical-align: middle;"><b><?=$crs['NAME']?></b></th>
                                    <?php }?>
                                    <?php /*if($from_date||$from_to){*/?><!--
                                        <th style="vertical-align: middle;">Количество обученных за период<?php /*if($from_date){*/?> с <?php /*=$from_date*/?><?php /*}*/?><?php /*if($from_to){*/?> по <?php /*=$from_to*/?><?php /*}*/?></th>
                                    <?php /*} else {*/?>
                                        <th style="vertical-align: middle;">Количество обученных</th>
                                    --><?php /*}*/?>
                                <?php }?>
                            </tr>
                            <tr>
                                <?php foreach ($all_manager_courses as $crs){?>
                                    <th class="text-center" style="vertical-align: middle;">Кол-во обуч. всего</th>
                                    <th class="text-center" style="vertical-align: middle;">% от общ. кол-ва</th>
                                    <?php if($from_date||$from_to){?>
                                        <th class="text-center" style="vertical-align: middle;">Кол-во обуч. <?php if($from_date){?> с <?=$from_date?><?php }?><?php if($from_to){?> по <?=$from_to?><?php }?></th>
                                    <?php }?>
                                <?php }?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($managers_info as $manager_name => $manager_info){
                                if($_REQUEST['direction'] == 'S01') {
                                    if(!in_array($manager_name, $all_mans['op']))
                                        continue;
                                }
                                if($_REQUEST['direction'] == 'A01') {
                                    if(!in_array($manager_name, $all_mans['ppo']))
                                        continue;
                                }
                                if($_REQUEST['direction'] == 'M01') {
                                    if(!in_array($manager_name, $all_mans['marketing']))
                                        continue;
                                }
                                if(!$manager_name)
                                    continue;?>
                            <tr style="height: 50px">
                                <td class="text-left"><?=$manager_name?></td>
                                <td  class="text-center"><?=$manager_info['EMPLOYESS_CNT']?></td>
                                <?php foreach ($manager_info['COURSES'] as $crs){?>
                                    <td class="text-center"><?=$crs['COMPLETED']?></td>
                                    <td class="text-center"><?=$crs['COMPLETED']==0?'0%':floor((int)$crs['COMPLETED']/$manager_info['EMPLOYESS_CNT']*100).'%'?></td>
                                    <?php if($from_date||$from_to){?>
                                        <td class="text-center"><?=$crs['PERIOD_COMPLETED']?></td>
                                    <?php }?>
                                <?php }?>
                            </tr>
                            <?php }?>
                        </tbody>
                    </table>
                    <div class="form-group" style="display: flex; padding-top: 1rem;">
                        <div class="btn-center">
                            <button class="btn" id="gen13"><span>Excel</span></button>
                        </div>
                    </div>
                    <table class="table table-bordered" id="table-report13" style="padding-top: 25px">
                        <thead class="thead-dark">
                            <tr>
                                <th rowspan="3" style="vertical-align: middle;">Город</th>
                                <th rowspan="3" style="vertical-align: middle;">Дилер</th>
                                <th rowspan="3" style="vertical-align: middle;">Код дилера</th>
                                <?php if($_REQUEST['direction'] == 'all') {?>
                                    <th rowspan="3" style="vertical-align: middle;">Регионал ОП</th>
                                    <th rowspan="3" style="vertical-align: middle;">Регионал ППО</th>
                                    <th rowspan="3" style="vertical-align: middle;">Регионал Маркетинг</th>
                                <?php } else {
                                    switch($_REQUEST['direction']){
                                        case 'S01':
                                            echo '<th rowspan="3" style="vertical-align: middle;">Регионал ОП</th>';
                                            break;
                                        case 'A01':
                                            echo '<th rowspan="3" style="vertical-align: middle;">Регионал ППО</th>';
                                            break;
                                        case 'M01':
                                            echo '<th rowspan="3" style="vertical-align: middle;">Регионал Маркетинг</th>';
                                            break;
                                    }
                                }?>
                                <?php foreach ($table_th as $role1) {?>
                                    <th colspan="<?=count($role1['COURSES'])*3+1?>" class="text-center" style="vertical-align: middle;"><b><?= Loc::getMessage('ROLE_COURSES') ?> "<?=$role1['NAME']?>"</b></th>
                                <?php }?>
                            </tr>
                            <tr>
                                <?php foreach ($table_th as $role2) {?>
                                    <th rowspan="2" style="vertical-align: middle; text-align: center"><?= Loc::getMessage('ALL_EMPLOYEES') ?></th>
                                    <?php foreach ($role2['COURSES'] as $course){?>
                                        <?php if($from_date||$from_to){?>
                                            <th colspan="3" style="vertical-align: middle;"><?=$course['NAME']?></th>
                                        <?php } else {?>
                                            <th colspan="2" style="vertical-align: middle;"><?=$course['NAME']?></th>
                                        <?php }?>
                                    <?php }?>
                                <?php }?>
                            </tr>
                            <tr>
                                <?php foreach ($table_th as $role2){?>
                                    <?php foreach ($role2['COURSES'] as $course_123){?>
                                        <th style="vertical-align: middle;">Кол-во обуч. всего</th>
                                        <th style="vertical-align: middle;">% от общ. кол-ва</th>
                                        <?php if($from_date||$from_to){?>
                                            <th style="vertical-align: middle;">Кол-во обуч. <?php if($from_date){?> с <?=$from_date?><?php }?><?php if($from_to){?> по <?=$from_to?><?php }?></th>
                                        <?php }?>
                                    <?php }?>
                                <?php }?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dealers as $dealer){?>
                            <tr style="height: 50px">
                                <td class="text-left"><?=$dealer['PROPERTY_CITY_VALUE']?></td>
                                <td class="text-left"><?=$dealer['NAME']?></td>
                                <td class="text-left"><?=$dealer['CODE']?></td>
                                <?php if($_REQUEST['direction'] == 'all') {?>
                                    <td style="vertical-align: middle;"><?=\Models\Dealer::getRegionalOP($dealer['ID'])?></td>
                                    <td style="vertical-align: middle;"><?=\Models\Dealer::getRegionalPPO($dealer['ID'])?></td>
                                    <td style="vertical-align: middle;"><?=\Models\Dealer::getRegionalMarketing($dealer['ID'])?></td>
                                <?php } else {
                                    switch($_REQUEST['direction']){
                                        case 'S01':
                                            echo '<td style="vertical-align: middle;">'.\Models\Dealer::getRegionalOP($dealer['ID']).'</td>';
                                            break;
                                        case 'A01':
                                            echo '<td style="vertical-align: middle;">'.\Models\Dealer::getRegionalPPO($dealer['ID']).'</td>';
                                            break;
                                        case 'M01':
                                            echo '<td style="vertical-align: middle;">'.\Models\Dealer::getRegionalMarketing($dealer['ID']).'</td>';
                                            break;
                                    }
                                }?>
                                <?php foreach ($table_th as $role__){
                                    $trole = $dealer['ROLES_LIST'][$role__['ID']];
                                    if(is_array($trole)){?>
                                    <td class="text-center"><b><?=check_full_array($trole['USERS'])?count($trole['USERS']):0?></b></td>
                                    <?php foreach ($role__['COURSES'] as $course_){
                                        $tcourse = $trole['COURSES'][$course_['ID']];
                                        if(is_array($tcourse)){?>
                                            <td><?=$tcourse['COMPLETED']?></td>
                                            <td><?=$tcourse['COMPLETED_PERCENTS']?></td>
                                            <?php if($from_date||$from_to){?>
                                                <td><?=$tcourse['PERIOD_COMPLETED']?></td>
                                            <?php }?>

                                        <?php }else{?>
                                            <td> - </td>
                                            <td> - </td>
                                            <td> - </td>
                                        <?php }?>
                                    <?php }
                                    } else { ?>
                                        <td><b> - </b></td>
                                        <?php foreach ($role__['COURSES'] as $course_уу){?>
                                            <td> - </td>
                                        <?php }
                                    }?>
                                <?php }?>
                            </tr>
                            <?php }?>
                        </tbody>
                    </table>
                    <button class="dt-button buttons-pdf buttons-html5" id="gen"><span>Excel</span></button>

                </div>
            </div>
        </div>
    </div>
</div>
<style>
    td{
        background-color: #fff!important;
    }
    tr:hover td{
        background-color: #bbbbbb!important;
    }
</style>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
