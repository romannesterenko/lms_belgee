<?php

use Bitrix\Main\Localization\Loc;
use Models\User;

const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$completions = new \Teaching\CourseCompletion();
$settings = \Settings\Reports::getByCode('courses_for_dealer');
$dealer_id = (int)$_REQUEST['dealer_id']?(int)$_REQUEST['dealer_id']:\Helpers\UserHelper::getDealerId();
$dealers = \Helpers\DealerHelper::getList(['ACTIVE' => 'Y'], ['ID', 'NAME', 'PROPERTY_CITY', 'CODE']);
$table_th = [];
$roles_array = [];
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
foreach ($dealers as &$dealer_) {
    $roles = [];
    $getListParams['select'] = ['ID', 'UF_ROLE', 'EMAIL'];
    $getListParams['filter'] = ['UF_DEALER' => $dealer_['ID']];
    if(check_full_array($roles_array)) {
        $getListParams['filter']['UF_ROLE'] = $roles_array;
        $roles = $roles_array;
    }
    $dealer_['EMPLOYEES'] = User::getArray($getListParams);

    //$dealer_['EMPLOYEES'] = \Models\Employee::getListByDealer($dealer_['ID'], ['ID', 'UF_ROLE']);
    /*foreach ($dealer_['EMPLOYEES'] as $EMPLOYEE)
        if(!check_full_array($roles_array)&&is_array($EMPLOYEE['UF_ROLE']))
            $roles = array_merge($roles, $EMPLOYEE['UF_ROLE']);*/
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
            $roles_arr[$r][] = $c;
        }
    }
    foreach ($dealer_['ROLES_LIST'] as &$role){
        $role['COURSES'] = $roles_arr[$role['ID']];
        if(check_full_array($role['COURSES'])&&count($role['COURSES'])>0)
            $table_th[$role['ID']] = $role;
        foreach ($dealer_['EMPLOYEES'] as $employee) {
            if (is_array($employee['UF_ROLE'])&&in_array($role['ID'], $employee['UF_ROLE']))
                $role['USERS'][] = $employee;
        }
        foreach ($role['COURSES'] as &$course){
            $course['COMPLETED'] = 0;
            foreach ($role['USERS'] as $user){
                if($completions->isCompleted($course['ID'], $user['ID']))
                    $course['COMPLETED']++;
            }
            $course['COMPLETED_PERCENTS'] = $course['COMPLETED']==0?'0%':floor($course['COMPLETED']/count($role['USERS'])*100).'%';
        }
    }
    $dealer_['EMPLOYESS_CNT'] = count($dealer_['EMPLOYEES']);
    //$dealer_['REGIONAL'] = \Models\Dealer::getRegional($dealer_['ID']);
    $dealer_['REGIONAL_OP'] = \Models\Dealer::getRegionalOP($dealer_['ID']);
    $dealer_['REGIONAL_PPO'] = \Models\Dealer::getRegionalPPO($dealer_['ID']);
}
//dump($dealers);
$_REQUEST['report_id'] = $dealer_id;?>
<div class="main-content">
    <aside>
        <div class="aside-sticky aside-sticky--title">
            <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
        </div>
    </aside>
    <div class="content">
        <div class="content-block">
            <input type="hidden" id="is_adaptive" value="<?=$settings['PROPERTIES']['IS_ADAPTIVE']?>">
            <input type="hidden" id="count" value="10">
            <div class="text-content text-content--long">
                <h2 class="h2 center">Отчет по ролям</h2>
                <div class="form-group" style="display: flex; padding-top: 1rem;">
                    <div class="btn-center">
                        <a href="report6_filter.php" class="btn">К генератору</a>
                    </div>
                </div>
                <div class="table-block">
                    <table class="table table-bordered" id="table-report" style="padding-top: 25px">
                        <thead class="thead-dark">
                            <tr>
                                <th rowspan="2" style="vertical-align: middle;"><?= Loc::getMessage('CITY') ?></th>
                                <th rowspan="2" style="vertical-align: middle;"><?= Loc::getMessage('NAME') ?></th>
                                <th rowspan="2" style="vertical-align: middle;">Код дилера</th>
                                <th rowspan="2" style="vertical-align: middle;"><?= Loc::getMessage('REGIONAL_OP') ?></th>
                                <th rowspan="2" style="vertical-align: middle;"><?= Loc::getMessage('REGIONAL_PPO') ?></th>
                                <?php foreach ($table_th as $role1){?>
                                    <th colspan="<?=count($role1['COURSES'])+1?>" class="text-center" style="vertical-align: middle;"><b><?= Loc::getMessage('ROLE_COURSES') ?> "<?=$role1['NAME']?>"</b></th>
                                <?php }?>
                            </tr>
                            <tr>
                                <?php foreach ($table_th as $role2){?>
                                    <th style="vertical-align: middle; text-align: center"><?= Loc::getMessage('ALL_EMPLOYEES') ?></th>
                                    <?php foreach ($role2['COURSES'] as $course){?>
                                        <th colspan="2" style="vertical-align: middle;"><?=$course['NAME']?></th>
                                        <th style="vertical-align: middle;">Количество обученных</th>
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
                                <td class="text-left"><?=$dealer['REGIONAL_OP']?></td>
                                <td class="text-left"><?=$dealer['REGIONAL_PPO']?></td>
                                <?php foreach ($table_th as $role__){
                                    $role = $dealer['ROLES_LIST'][$role__['ID']];
                                    if(is_array($role)){?>
                                    <td><b><?=check_full_array($role['USERS'])?count($role['USERS']):0?></b></td>
                                    <?php foreach ($role__['COURSES'] as $course_){
                                        $course = $role['COURSES'][$course_['ID']];
                                        if(is_array($course)){?>
                                            <td><?=$course['COMPLETED']?></td>
                                            <td><?=$course['COMPLETED_PERCENTS']?></td>
                                            <td><?=$course['COMPLETED']?></td>
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
