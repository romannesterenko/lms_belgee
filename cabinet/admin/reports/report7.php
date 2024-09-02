<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Localization\Loc;
use Helpers\DealerHelper;
use Helpers\PageHelper;
use Models\Dealer;
use Models\Role;
use Models\User;
use Teaching\CourseCompletion;

global $APPLICATION, $USER;
$recruits = new \Teaching\Recruitment();
$dealer_id = (int)$_REQUEST['dealer_id'];
$r_city = $_REQUEST['city'];

$getListParams['select'] = ['ID', 'UF_DEALER', 'UF_ROLE', 'UF_WORK_START_DATE'];
$getListParams['filter'] = ['>ID' => 2, '!UF_ROLE' => false, '!UF_DEALER' => false];
if(check_full_array($_REQUEST['dealer_names'])) {
    $getListParams['filter']['UF_DEALER'] = $_REQUEST['dealer_names'];
    $r_city = '';
} else {
    if (check_full_array($r_city)) {
        $dealers_request = Dealer::getByCity($r_city, ['ID']);
        if (count($dealers_request) > 0) {
            foreach ($dealers_request as $item)
                $getListParams['filter']['UF_DEALER'][] = $item['ID'];
        }
    } else {
        $getListParams['filter']['UF_DEALER'] = \Helpers\UserHelper::getDealerId();
    }
}

$all_dealers = [];
$cities = [];
foreach (DealerHelper::getList(['ACTIVE' => 'Y'], ['ID', 'NAME', 'PROPERTY_CITY']) as $dealer){
    $all_dealers[$dealer['ID']] = $dealer;
    if(!in_array($dealer['PROPERTY_CITY_VALUE'], $cities))
        $cities[] = $dealer['PROPERTY_CITY_VALUE'];
    unset($dealer);
}

if (check_full_array($_REQUEST['role'])){
    $getListParams['filter']['UF_ROLE'] = $_REQUEST['role'];
}
$users = User::getArray($getListParams);

$roles = [];
$role_ids = [];
$dealers = [];
foreach ($users as $user){
    if (check_full_array($_REQUEST['role'])){
        $user['UF_ROLE'] = $_REQUEST['role'];
    }
    $role_ids = array_merge($role_ids, $user['UF_ROLE']);
    $dealers[$user['UF_DEALER']]['DEALER'] = $all_dealers[$user['UF_DEALER']];
    $dealers[$user['UF_DEALER']]['USERS'][] = $user;
}
if (check_full_array($_REQUEST['role'])){
    $role_ids = $_REQUEST['role'];
}
$from = "";
$to = "";
$role_array = Role::getAllArray(['ID' => array_unique($role_ids)]);
if($_REQUEST['from_month']&&$_REQUEST['from_year']){
    if($_REQUEST['from_year']!='all'){
        $month = $_REQUEST['from_month']=='all'?"01.":$_REQUEST['from_month'].".";
        $from = "01.".$month.$_REQUEST['from_year']." 00:00:00";
    }
}
if($_REQUEST['to_month']&&$_REQUEST['to_year']){
    if($_REQUEST['to_year']!='all'){
        $month = $_REQUEST['to_month']=='all'?"12.":$_REQUEST['to_month'].".";
        $day = date("t", strtotime("01.".$month.$_REQUEST['to_year']." 23:59:59"));
        $to = $day.".".$month.$_REQUEST['to_year']." 23:59:59";
    }
}

if($to=='')
    $to = date('t.m.Y 23:59:59');
if($from=='')
    $from = date('01.01.2019 23:59:59');
$start = new \DateTime($from);
$end   = new \DateTime($to);
$interval = $end->diff($start);
$days   = $interval->days;
$months = $interval->y * 12 + $interval->m;
$years  = intval($end->format('Y')) - intval($start->format('Y'));
$period = new DatePeriod($start, new DateInterval('P1M'), $months);
$result = [];
foreach ($period as $date) {
    $result[] = $date->format('t.m.Y 23:59:59');
}
$result = array_reverse($result);
foreach ($dealers as $dealer){
    foreach ($dealer['USERS'] as $user){
        foreach ($user['UF_ROLE'] as $role_u){
            $roles[$dealer['DEALER']['ID'].'_'.$role_u]['CITY'] = $dealer['DEALER']['PROPERTY_CITY_VALUE'];
            $roles[$dealer['DEALER']['ID'].'_'.$role_u]['DEALER'] = $dealer['DEALER']['NAME'];
            if(empty($roles[$dealer['DEALER']['ID'].'_'.$role_u]['REGIONAL']))
                $roles[$dealer['DEALER']['ID'].'_'.$role_u]['REGIONAL'] = Dealer::getRegional($dealer['DEALER']['ID']);
            if(empty($roles[$dealer['DEALER']['ID'].'_'.$role_u]['REGIONAL_OP']))
                $roles[$dealer['DEALER']['ID'].'_'.$role_u]['REGIONAL_OP'] = Dealer::getRegionalOP($dealer['DEALER']['ID']);
            if(empty($roles[$dealer['DEALER']['ID'].'_'.$role_u]['REGIONAL_PPO']))
                $roles[$dealer['DEALER']['ID'].'_'.$role_u]['REGIONAL_PPO'] = Dealer::getRegionalPPO($dealer['DEALER']['ID']);
            $roles[$dealer['DEALER']['ID'].'_'.$role_u]['ROLE'] = $role_array[$role_u]??$role_u;
            $roles[$dealer['DEALER']['ID'].'_'.$role_u]['ROLE_ID'] = $role_u;
            $roles[$dealer['DEALER']['ID'].'_'.$role_u]['NOW_USERS'][] = $user;
        }
    }
}
$all_dismiss_by_dealer = [];
$all_recruit_by_dealer = [];
foreach ($roles as $d_r_string => &$role){
    $role['NEW_USERS'] = [];
    $role['KICKED_USERS'] = [];
    $arr_d_r = explode('_', $d_r_string);
    $dealer_id = $arr_d_r[0];
    $r_id = $arr_d_r[1];
    if(!check_full_array($all_dismiss_by_dealer[$dealer_id]))
        $all_dismiss_by_dealer[$dealer_id] = (new \Teaching\Recruitment())->get(['UF_DEALER' => $dealer_id, 'UF_TYPE' => 27, "!UF_ROLES" => false]);
    if(!check_full_array($all_recruit_by_dealer[$dealer_id]))
        $all_recruit_by_dealer[$dealer_id] = (new \Teaching\Recruitment())->get(['UF_DEALER' => $dealer_id, 'UF_TYPE' => 26, "!UF_ROLES" => false]);
    $now_users = count($role['NOW_USERS']);
    foreach ($result as $date){
        foreach ($all_dismiss_by_dealer[$dealer_id] as $key => $dismiss){
            if(in_array($r_id, $dismiss['UF_ROLES'])){
                if(strtotime($dismiss['UF_TIME']->format('d.m.Y H:i:s'))>strtotime($date)) {
                    $role['KICKED_USERS'][] = $dismiss;
                    $now_users++;
                    unset($all_dismiss_by_dealer[$dealer_id][$key]);
                }
            }
        }
        foreach ($all_recruit_by_dealer[$dealer_id] as $key => $recruit){
            if(in_array($r_id, $recruit['UF_ROLES'])){
                if(strtotime($recruit['UF_TIME']->format('d.m.Y H:i:s'))>strtotime($date)) {
                    $role['NEW_USERS'][] = $recruit;
                    $now_users--;
                    unset($all_recruit_by_dealer[$dealer_id][$key]);
                }
            }
        }
        $role['COUNT_USERS_PERIOD'][$date] = $now_users;

        $role['COMMON_VALUE_USERS_PERIOD'][$date] = round(array_sum($role['COUNT_USERS_PERIOD'])/count($role['COUNT_USERS_PERIOD']), 2);
        $role['TEK_KADR_PERIOD'][$date] = $role['COMMON_VALUE_USERS_PERIOD'][$date]>0?(round(count($role['KICKED_USERS'])/$role['COMMON_VALUE_USERS_PERIOD'][$date]*100)):0;
    }
    $role['START_PERIOD_USERS'] = $role['COUNT_USERS_PERIOD'][$result[count($result)-1]];
    $role['END_PERIOD_USERS'] = $role['COUNT_USERS_PERIOD'][$result[0]];
    $role['COMMON_VALUE_USERS'] = round(array_sum($role['COUNT_USERS_PERIOD'])/count($role['COUNT_USERS_PERIOD']), 2);
    $role['TEK_KADR'] = $role['COMMON_VALUE_USERS']>0?(round(count($role['KICKED_USERS'])/$role['COMMON_VALUE_USERS']*100))."%":"0%";
    $kicked_user_ids = [];
    foreach ($role['KICKED_USERS'] as $KICKED_USER){
        $kicked_user_ids[] = $KICKED_USER['UF_USER'];
    }
    $role['COURSES_BY_KICKED_USERS'] = count((new CourseCompletion())->get(['UF_USER_ID' => array_unique($kicked_user_ids), 'UF_IS_COMPLETE' => 1, '>UF_DATE' => $from, '<UF_DATE' => $to]));
}

$c_role = [];
$_REQUEST['report_id'] = 1;?>
<div class="main-content">
    <aside>
        <div class="aside-sticky aside-sticky--title">
            <?php require_once(PageHelper::getSideBarForCabinet());?>
        </div>
    </aside>
    <div class="content">
        <div class="content-block">
            <div class="text-content text-content--long">
                <h2 class="h2 center">Отчет по текучести кадров</h2>
                <div class="table-block">
                    <table class="table table-bordered" id="table-report" style="padding-top: 25px">
                        <thead class="thead-dark">
                            <tr>
                                <th style="vertical-align: middle" class="text-center"><?= Loc::getMessage('TABLE_TH_CITY') ?></th>
                                <th style="vertical-align: middle" class="text-center"><?= Loc::getMessage('TABLE_TH_DEALER') ?></th>
                                <th style="vertical-align: middle" class="text-center"><?= Loc::getMessage('TABLE_TH_REGIONAL_OP') ?></th>
                                <th style="vertical-align: middle" class="text-center"><?= Loc::getMessage('TABLE_TH_REGIONAL_PPO') ?></th>
                                <th style="vertical-align: middle" class="text-center"><?= Loc::getMessage('TABLE_TH_ROLE') ?></th>
                                <th style="vertical-align: middle" class="text-center">Количество на начало периода</th>
                                <th style="vertical-align: middle" class="text-center">Новые сотрудники</th>
                                <th style="vertical-align: middle" class="text-center">Уволенные</th>
                                <th style="vertical-align: middle" class="text-center">Количество на конец периода</th>
                                <th style="vertical-align: middle" class="text-center">Среднее за период</th>
                                <th style="vertical-align: middle" class="text-center">Текучесть кадров (среднее)</th>
                                <th style="vertical-align: middle" class="text-center"><?= Loc::getMessage('TABLE_TH_TRAININH_COUNT') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roles as $role__){?>
                                <tr>
                                    <td><?=$role__['CITY']?></td>
                                    <td><?=$role__['DEALER']?></td>
                                    <td><?=$role__['REGIONAL_OP']?></td>
                                    <td><?=$role__['REGIONAL_PPO']?></td>
                                    <td><?=$role__['ROLE']?></td>
                                    <td><?=$role__['START_PERIOD_USERS']?></td>
                                    <td><?=count($role__['NEW_USERS'])?></td>
                                    <td><?=count($role__['KICKED_USERS'])?></td>
                                    <td><?=$role__['END_PERIOD_USERS']?></td>
                                    <td><?=$role__['COMMON_VALUE_USERS']?></td>
                                    <td style="white-space: nowrap"><?=$role__['TEK_KADR']?> (<?=count($role__['TEK_KADR_PERIOD'])>0?ceil((array_sum($role__['TEK_KADR_PERIOD'])/count($role__['TEK_KADR_PERIOD']))):0?>%)</td>
                                    <td><?=$role__['COURSES_BY_KICKED_USERS']?></td>
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
