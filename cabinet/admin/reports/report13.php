<?php

use Bitrix\Main\Localization\Loc;
use Models\User;

const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$completions = new \Teaching\CourseCompletion();
$settings = \Settings\Reports::getByCode('courses_for_dealer');
$dealer_id = (int)$_REQUEST['dealer_id']?(int)$_REQUEST['dealer_id']:\Helpers\UserHelper::getDealerId();
$dealers = \Helpers\DealerHelper::getList(['ACTIVE' => 'Y', '!ID' => 360], ['ID', 'NAME', 'PROPERTY_CITY', 'CODE']);
$table_th = [];
$roles_array = [];
dump($_REQUEST);
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
if($_REQUEST['op_servis_op']=='on'||$_REQUEST['op_servis_servis']=='on'){
    if($_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']!='on'){
        $roles_array = \Models\Role::getArray(['SECTION_ID' => 2, 'INCLUDE_SUBSECTIONS' => 'Y']);
        $roles_array = check_full_array($roles_array)?array_keys($roles_array):[];
    }
    if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']=='on'){
        $roles_array = \Models\Role::getArray(['SECTION_ID' => 3, 'INCLUDE_SUBSECTIONS' => 'Y']);
        $roles_array = check_full_array($roles_array)?array_keys($roles_array):[];
    }
}
if(check_full_array($_REQUEST['role']))
    $roles_array = $_REQUEST['role'];
$need_request = true;
if(check_full_array($roles_array)){
    $all_roles_list = \Teaching\Roles::getRolesList(['ID' => array_unique($roles_array)], ['ID', 'NAME']);
    $all_courses_list = \Teaching\Courses::getByRoleArray(array_keys($all_roles_list), ['ID', 'NAME']);
    $need_request = false;
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
    if($dealer_['REGIONAL_OP']&&!in_array($dealer_['REGIONAL_OP'], $all_mans['op'])){
        $all_mans['op'][] = $dealer_['REGIONAL_OP'];
    }
    if($dealer_['REGIONAL_PPO']&&!in_array($dealer_['REGIONAL_PPO'], $all_mans['ppo'])){
        $all_mans['ppo'][] = $dealer_['REGIONAL_PPO'];
    }
    $need_ppo = true;
    $need_op = true;
    /*if($_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']!='on'){
        $need_ppo = false;
    }
    if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']=='on'){
        $need_op = false;
    }*/
    if($need_op&&$dealer_['REGIONAL_OP']&&$dealer_['REGIONAL_OP']!=''){

        $managers_info[$dealer_['REGIONAL_OP']]['EMPLOYESS_CNT'] = (int)$managers_info[$dealer_['REGIONAL_OP']]['EMPLOYESS_CNT'];
        $managers_info[$dealer_['REGIONAL_OP']]['EMPLOYESS_CNT']+=$dealer_['EMPLOYESS_CNT'];

    }
    if($need_ppo&&$dealer_['REGIONAL_PPO']&&$dealer_['REGIONAL_PPO']!=''){

        $managers_info[$dealer_['REGIONAL_PPO']]['EMPLOYESS_CNT'] = (int)$managers_info[$dealer_['REGIONAL_PPO']]['EMPLOYESS_CNT'];
        $managers_info[$dealer_['REGIONAL_PPO']]['EMPLOYESS_CNT']+=$dealer_['EMPLOYESS_CNT'];
    }
    if($need_request) {
        $dealer_['ROLES_LIST'] = \Teaching\Roles::getRolesList(['ID' => array_unique($roles)], ['ID', 'NAME']);
        $dealer_['COURSES'] = \Teaching\Courses::getByRoleArray(array_keys($dealer_['ROLES_LIST']), ['ID', 'NAME']);
    } else {
        $dealer_['ROLES_LIST'] = $all_roles_list;
        $dealer_['COURSES'] = $all_courses_list;
    }

    $roles_arr = [];
    foreach ($dealer_['COURSES'] as $c){
        foreach ($c['PROPERTY_ROLES_VALUE'] as $r){
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
        foreach ($role['COURSES'] as &$course){
            if(!check_full_array($completions_array[$course['ID']])) {
                $completions_filter = $period_completions_filter = ['UF_COURSE_ID' => $course['ID'], 'UF_IS_COMPLETE' => 1];
                if($from_date)
                    $period_completions_filter['>UF_COMPLETED_TIME'] = $from_date." 00:00:01";
                if($from_to)
                    $period_completions_filter['<UF_COMPLETED_TIME'] = $from_to." 23:59:59";
                $completions = (new \Teaching\CourseCompletion())->get($completions_filter);
                $period_completions = (new \Teaching\CourseCompletion())->get($period_completions_filter);
                foreach ($completions as $completion){
                    $completions_array[$completion['UF_COURSE_ID']][$completion['UF_USER_ID']] = $completion;
                }
                foreach ($period_completions as $period_completion){
                    $period_completions_array[$period_completion['UF_COURSE_ID']][$period_completion['UF_USER_ID']] = $period_completion;
                }
                $complete_items = $completions_array[$course['ID']];
                $period_complete_items = $period_completions_array[$course['ID']];
            } else {
                $complete_items = $completions_array[$course['ID']];
                $period_complete_items = $period_completions_array[$course['ID']];
            }
            if($need_ppo) {
                $managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$course['ID']]['NAME'] = $course['NAME'];
                $managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$course['ID']]['COMPLETED'] = (int)$managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$course['ID']]['COMPLETED'];
                $managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$course['ID']]['PERIOD_COMPLETED'] = (int)$managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$course['ID']]['PERIOD_COMPLETED'];
            }
            if($need_op) {
                $managers_info[$dealer_['REGIONAL_OP']]['COURSES'][$course['ID']]['NAME'] = $course['NAME'];
                $managers_info[$dealer_['REGIONAL_OP']]['COURSES'][$course['ID']]['COMPLETED'] = (int)$managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$course['ID']]['COMPLETED'];
                $managers_info[$dealer_['REGIONAL_OP']]['COURSES'][$course['ID']]['PERIOD_COMPLETED'] = (int)$managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$course['ID']]['PERIOD_COMPLETED'];
            }
            $course['COMPLETED'] = 0;
            $course['PERIOD_COMPLETED'] = 0;
            foreach ($role['USERS'] as $user){
                if(check_full_array($complete_items[$user['ID']])) {
                    $course['COMPLETED']++;
                    if($need_ppo) {
                        $managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$course['ID']]['COMPLETED']++;
                    }
                    if($need_op) {
                        $managers_info[$dealer_['REGIONAL_OP']]['COURSES'][$course['ID']]['COMPLETED']++;
                    }
                }
                if(check_full_array($period_complete_items[$user['ID']])) {
                    $course['PERIOD_COMPLETED']++;
                    if($need_ppo) {
                        $managers_info[$dealer_['REGIONAL_PPO']]['COURSES'][$course['ID']]['PERIOD_COMPLETED']++;
                    }
                    if($need_op) {
                        $managers_info[$dealer_['REGIONAL_OP']]['COURSES'][$course['ID']]['PERIOD_COMPLETED']++;
                    }
                }
            }
            $dealer_courses[$course['ID']] = ['NAME' => $course['NAME']];
            $dealer_courses[$course['ID']]['COMPLETED'] = (int)$dealer_courses[$course['ID']]['COMPLETED'];
            $dealer_courses[$course['ID']]['COMPLETED']+=$course['COMPLETED'];
            $course['COMPLETED_PERCENTS'] = $course['COMPLETED']==0?'0%':floor($course['COMPLETED']/count($role['USERS'])*100).'%';
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
//dump($all_mans);
$_REQUEST['report_id'] = $dealer_id;?>
<div class="main-content">
    <div class="content">
        <div class="content-block">
            <input type="hidden" id="is_adaptive" value="<?=$settings['PROPERTIES']['IS_ADAPTIVE']?>">
            <input type="hidden" id="count" value="10">
            <div class="text-content text-content--long">
                <h2 class="h2 center">Отчет по ролям</h2>
                <div class="form-group" style="display: flex; padding-top: 1rem;">
                    <div class="btn-center">
                        <a href="report13_filter.php" class="btn">К генератору</a>
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
                                <?php foreach ($all_manager_courses as $crs){?>
                                    <?php if($from_date||$from_to){?>
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
                                if($_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']!='on'){
                                    if(!in_array($manager_name, $all_mans['op']))
                                        continue;
                                }
                                if($_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']=='on'){
                                    if(!in_array($manager_name, $all_mans['ppo']))
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
                                <th rowspan="3" style="vertical-align: middle;"><?= Loc::getMessage('CITY') ?></th>
                                <th rowspan="3" style="vertical-align: middle;"><?= Loc::getMessage('NAME') ?></th>
                                <th rowspan="3" style="vertical-align: middle;">Код дилера</th>
                                <?php if($_REQUEST['op_servis_op']=='on'||(empty($_REQUEST['op_servis_op'])&&empty($_REQUEST['op_servis_servis']))){?>
                                    <th rowspan="3" style="vertical-align: middle;"><?= Loc::getMessage('REGIONAL_OP') ?></th>
                                <?php }?>
                                <?php if($_REQUEST['op_servis_servis']=='on'||(empty($_REQUEST['op_servis_op'])&&empty($_REQUEST['op_servis_servis']))){?>
                                    <th rowspan="3" style="vertical-align: middle;"><?= Loc::getMessage('REGIONAL_PPO') ?></th>
                                <?php }?>
                                <?php foreach ($table_th as $role1){?>
                                    <th colspan="<?=count($role1['COURSES'])*3+1?>" class="text-center" style="vertical-align: middle;"><b><?= Loc::getMessage('ROLE_COURSES') ?> "<?=$role1['NAME']?>"</b></th>
                                <?php }?>
                            </tr>
                            <tr>
                                <?php foreach ($table_th as $role2){?>
                                    <th rowspan="2" style="vertical-align: middle; text-align: center"><?= Loc::getMessage('ALL_EMPLOYEES') ?></th>
                                    <?php foreach ($role2['COURSES'] as $course){?>
                                        <?php if($from_date||$from_to){?>
                                            <th colspan="3" style="vertical-align: middle;"><?=$course['NAME']?></th>
                                        <?php } else {?>
                                            <th colspan="2" style="vertical-align: middle;"><?=$course['NAME']?></th>
                                        <?php }?>

                                        <?php /* if($from_date||$from_to){?>
                                            <th style="vertical-align: middle;">Количество обученных за период<?php if($from_date){?> с <?=$from_date?><?php }?><?php if($from_to){?> по <?=$from_to?><?php }?></th>
                                        <?php } else {?>
                                            <th style="vertical-align: middle;">Количество обученных</th>
                                        <?php }*/?>
                                    <?php }?>
                                <?php }?>
                            </tr>
                            <tr>
                                <?php foreach ($table_th as $role2){?>
                                    <?php foreach ($role2['COURSES'] as $course){?>
                                        <th style="vertical-align: middle;">Кол-во обуч. всего</th>
                                        <th style="vertical-align: middle;">% от общ. кол-ва</th>
                                        <?php if($from_date||$from_to){?>
                                            <th style="vertical-align: middle;">Кол-во обуч. <?php if($from_date){?> с <?=$from_date?><?php }?><?php if($from_to){?> по <?=$from_to?><?php }?></th>
                                        <?php }?>

                                        <?php /*if($from_date||$from_to){?>
                                            <th style="vertical-align: middle;">Количество обученных за период<?php if($from_date){?> с <?=$from_date?><?php }?><?php if($from_to){?> по <?=$from_to?><?php }?></th>
                                        <?php } else {?>
                                            <th style="vertical-align: middle;">Количество обученных</th>
                                        <?php }*/?>
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
                                <?php if($_REQUEST['op_servis_op']=='on'||(empty($_REQUEST['op_servis_op'])&&empty($_REQUEST['op_servis_servis']))){?>
                                    <td class="text-left"><?=$dealer['REGIONAL_OP']?></td>                                <?php }?>
                                <?php if($_REQUEST['op_servis_servis']=='on'||(empty($_REQUEST['op_servis_op'])&&empty($_REQUEST['op_servis_servis']))){?>
                                    <td class="text-left"><?=$dealer['REGIONAL_PPO']?></td>
                                <?php }?>
                                <?php foreach ($table_th as $role__){
                                    $role = $dealer['ROLES_LIST'][$role__['ID']];
                                    if(is_array($role)){?>
                                    <td class="text-center"><b><?=check_full_array($role['USERS'])?count($role['USERS']):0?></b></td>
                                    <?php foreach ($role__['COURSES'] as $course_){
                                        $course = $role['COURSES'][$course_['ID']];
                                        if(is_array($course)){?>
                                            <td><?=$course['COMPLETED']?></td>
                                            <td><?=$course['COMPLETED_PERCENTS']?></td>
                                            <?php if($from_date||$from_to){?>
                                                <td><?=$course['PERIOD_COMPLETED']?></td>
                                            <?php }?>

                                        <?php }else{?>
                                            <td> - </td>
                                            <td> - </td>
                                            <td> - </td>
                                        <?php }?>
                                    <?php }
                                    }else{?>
                                        <td><b> - </b></td>
                                        <?php foreach ($role__['COURSES'] as $course){?>
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
