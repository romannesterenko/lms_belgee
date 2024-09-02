<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Helpers\DealerHelper;
use Models\Dealer;
use Models\Role;
use Models\User;
use Teaching\CourseCompletion;

global $APPLICATION, $USER;

if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");


$recruits = new \Teaching\Recruitment();
\Helpers\Log::write(['user' => $USER->GetID(), 'report' => '7', 'request' => $_REQUEST]);
$dealer_id = (int)$_REQUEST['dealer_id'];
$r_city = $_REQUEST['city'];

$all_users = User::getAll();

$getListParams['select'] = ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'UF_ROLE', 'UF_WORK_START_DATE'];
$getListParams['filter'] = ['>ID' => 2, '!UF_ROLE' => false, 'ACTIVE' => 'Y', '!UF_DEALER' => false];


if (check_full_array($_REQUEST['city'])) {
    $dealers_request = Dealer::getByCity($_REQUEST['city'], ['ID']);
    if (count($dealers_request) > 0) {
        foreach ($dealers_request as $item) {
            $getListParams['filter']['UF_DEALER'][] = $item['ID'];
        }
    }
}
if(check_full_array($_REQUEST['regional_op'])||check_full_array($_REQUEST['regional_ppo'])||check_full_array($_REQUEST['regional_marketing'])){
    $getListParams['filter']['UF_DEALER'] = $getListParams['filter']['UF_DEALER']??[];
    $dealers = [];
    if(check_full_array($_REQUEST['regional_ppo'])) {
        $dealers = \Models\Dealer::getByRegionalPPO($_REQUEST['regional_ppo']);
    }
    if(check_full_array($_REQUEST['regional_op'])) {
        $dealers = \Models\Dealer::getByRegionalOP($_REQUEST['regional_op']);
    }
    if(check_full_array($_REQUEST['regional_marketing'])){
        $dealers = \Models\Dealer::getByRegionalMarketing($_REQUEST['regional_marketing']);
    }
    if (check_full_array($dealers)) {
        if(check_full_array($getListParams['filter']['UF_DEALER']))
            $getListParams['filter']['UF_DEALER'] = array_intersect($getListParams['filter']['UF_DEALER'], array_keys($dealers));
        else
            $getListParams['filter']['UF_DEALER'] = array_keys($dealers);
    }

}
if(check_full_array($_REQUEST['dealer_names'])) {
    $getListParams['filter']['UF_DEALER'] = $_REQUEST['dealer_names'];
}
$all_dealers = [];
$cities = [];
foreach (DealerHelper::getList(['ACTIVE' => 'Y'], ['ID', 'NAME', 'PROPERTY_CITY']) as $dealer){
    $all_dealers[$dealer['ID']] = $dealer;
    if(!in_array($dealer['PROPERTY_CITY_VALUE'], $cities))
        $cities[] = $dealer['PROPERTY_CITY_VALUE'];
    unset($dealer);
}


switch ($_REQUEST['direction']) {
    case 'S01':
        $roles = \Models\Role::getArray(['SECTION_ID' => 2, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($roles))
            $getListParams['filter']['UF_ROLE'] = array_keys($roles);
        break;
    case 'A01':
        $roles = \Models\Role::getArray(['SECTION_ID' => 3, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($roles))
            $getListParams['filter']['UF_ROLE'] = array_keys($roles);
        break;
    case 'M01':
        $roles = \Models\Role::getArray(['SECTION_ID' => 139, 'INCLUDE_SUBSECTIONS' => 'Y']);
        if(check_full_array($roles))
            $getListParams['filter']['UF_ROLE'] = array_keys($roles);
        break;
    case 'all':
        $roles = \Models\Role::getAll(['ID', 'NAME']);
        if(check_full_array($roles))
            $getListParams['filter']['UF_ROLE'] = array_keys($roles);
        break;
}

if (check_full_array($_REQUEST['role'])){
    $getListParams['filter']['UF_ROLE'] = $_REQUEST['role'];
}
if (check_full_array($getListParams['filter']['UF_ROLE'])) {
    $users = User::getArray($getListParams);
    $roles = [];
    $role_ids = [];
    $dealers = [];
    foreach ($users as $user) {
        if (check_full_array($_REQUEST['role'])) {
            $user['UF_ROLE'] = $_REQUEST['role'];
        }
        $role_ids = array_merge($role_ids, $user['UF_ROLE']);
        $dealers[$user['UF_DEALER']]['DEALER'] = $all_dealers[$user['UF_DEALER']];
        $dealers[$user['UF_DEALER']]['USERS'][] = $user;
    }
    if (check_full_array($_REQUEST['role'])) {
        $role_ids = $_REQUEST['role'];
    }
    $from = "";
    $to = "";
    $role_array = Role::getAllArray(['ID' => array_unique($role_ids)]);
    if ($_REQUEST['from_month'] && $_REQUEST['from_year']) {
        if ($_REQUEST['from_year'] != 'all') {
            $month = $_REQUEST['from_month'] == 'all' ? "01." : $_REQUEST['from_month'] . ".";
            $from = "01." . $month . $_REQUEST['from_year'] . " 00:00:00";
        }
    }
    if ($_REQUEST['to_month'] && $_REQUEST['to_year']) {
        if ($_REQUEST['to_year'] != 'all') {
            $month = $_REQUEST['to_month'] == 'all' ? "12." : $_REQUEST['to_month'] . ".";
            $day = date("t", strtotime("01." . $month . $_REQUEST['to_year'] . " 23:59:59"));
            $to = $day . "." . $month . $_REQUEST['to_year'] . " 23:59:59";
        }
    }
    if ($to == '')
        $to = date('t.m.Y 23:59:59');
    if ($from == '')
        $from = date('01.01.2019 23:59:59');
    $start = new \DateTime($from);
    $end = new \DateTime($to);
    $interval = $end->diff($start);
    $days = $interval->days;
    $months = $interval->y * 12 + $interval->m;
    $years = intval($end->format('Y')) - intval($start->format('Y'));
    $period = new DatePeriod($start, new DateInterval('P1M'), $months);
    $result = [];
    foreach ($period as $date) {
        $result[] = $date->format('t.m.Y 23:59:59');
    }
    $result = array_reverse($result);
    foreach ($dealers as $dealer) {
        foreach ($dealer['USERS'] as $user) {
            foreach ($user['UF_ROLE'] as $role_u) {
                $roles[$dealer['DEALER']['ID'] . '_' . $role_u]['CITY'] = $dealer['DEALER']['PROPERTY_CITY_VALUE'];
                $roles[$dealer['DEALER']['ID'] . '_' . $role_u]['DEALER'] = $dealer['DEALER']['NAME'];
                if (empty($roles[$dealer['DEALER']['ID'] . '_' . $role_u]['REGIONAL']))
                    $roles[$dealer['DEALER']['ID'] . '_' . $role_u]['REGIONAL'] = Dealer::getRegional($dealer['DEALER']['ID']);
                if (empty($roles[$dealer['DEALER']['ID'] . '_' . $role_u]['REGIONAL_OP']))
                    $roles[$dealer['DEALER']['ID'] . '_' . $role_u]['REGIONAL_OP'] = Dealer::getRegionalOP($dealer['DEALER']['ID']);
                if (empty($roles[$dealer['DEALER']['ID'] . '_' . $role_u]['REGIONAL_PPO']))
                    $roles[$dealer['DEALER']['ID'] . '_' . $role_u]['REGIONAL_PPO'] = Dealer::getRegionalPPO($dealer['DEALER']['ID']);
                if (empty($roles[$dealer['DEALER']['ID'] . '_' . $role_u]['REGIONAL_MARKETING']))
                    $roles[$dealer['DEALER']['ID'] . '_' . $role_u]['REGIONAL_MARKETING'] = Dealer::getRegionalMarketing($dealer['DEALER']['ID']);
                $roles[$dealer['DEALER']['ID'] . '_' . $role_u]['ROLE'] = $role_array[$role_u] ?? $role_u;
                $roles[$dealer['DEALER']['ID'] . '_' . $role_u]['ROLE_ID'] = $role_u;
                $roles[$dealer['DEALER']['ID'] . '_' . $role_u]['NOW_USERS'][] = $user;
            }
        }
    }
    $all_dismiss_by_dealer = [];
    $all_recruit_by_dealer = [];
    $act_users = [];
    foreach ($roles as $d_r_string => &$role) {
        $role['NEW_USERS'] = [];
        $role['KICKED_USERS'] = [];
        $arr_d_r = explode('_', $d_r_string);
        $dealer_id = $arr_d_r[0];
        $r_id = $arr_d_r[1];
        if (!check_full_array($all_dismiss_by_dealer[$dealer_id]))
            $all_dismiss_by_dealer[$dealer_id] = (new \Teaching\Recruitment())->get(['UF_ROLES' => $r_id, 'UF_DEALER' => $dealer_id, 'UF_TYPE' => 27, "!UF_ROLES" => false, 'UF_DELETED' => false]);
        if (!check_full_array($all_recruit_by_dealer[$dealer_id]))
            $all_recruit_by_dealer[$dealer_id] = (new \Teaching\Recruitment())->get(['UF_ROLES' => $r_id, 'UF_DEALER' => $dealer_id, 'UF_TYPE' => 26, "!UF_ROLES" => false, 'UF_DELETED' => false]);
        $now_users = count($role['NOW_USERS']);
        $role_tmp_arr = $role['NOW_USERS'];
        foreach ($role_tmp_arr as $now_user_item) {
            $role['NOW_USERS_ARRAY'][$now_user_item['ID']] = $now_user_item;
        }
        foreach ($result as $date) {
            foreach ($all_recruit_by_dealer[$dealer_id] as $key => $recruit) {
                if (in_array($r_id, $recruit['UF_ROLES'])) {
                    if (strtotime($recruit['UF_TIME']->format('d.m.Y H:i:s')) > strtotime($date)) {
                        if (strtotime($recruit['UF_TIME']->format('d.m.Y H:i:s')) > strtotime(current($result))) {
                            $role['RECRUIT_WITHOUT_PERIOD'][] = $recruit;
                        } else {
                            $role['NEW_USERS'][] = $recruit;
                        }
                        if (check_full_array($role['NOW_USERS_ARRAY'][$recruit['UF_USER']])) {
                            unset($role['NOW_USERS_ARRAY'][$recruit['UF_USER']]);
                        }
                        $now_users = count($role['NOW_USERS_ARRAY']);
                        unset($all_recruit_by_dealer[$dealer_id][$key]);
                    }
                }
            }
            foreach ($all_dismiss_by_dealer[$dealer_id] as $key => $dismiss) {
                if (in_array($r_id, $dismiss['UF_ROLES'])) {
                    if (strtotime($dismiss['UF_TIME']->format('d.m.Y H:i:s')) > strtotime($date)) {
                        if (strtotime($dismiss['UF_TIME']->format('d.m.Y H:i:s')) > strtotime(current($result))) {
                            $role['DISMISS_WITHOUT_PERIOD'][] = $dismiss;
                        } else {
                            $role['KICKED_USERS'][] = $dismiss;
                        }
                        if (!check_full_array($role['NOW_USERS_ARRAY'][$dismiss['UF_USER']])) {
                            $role['NOW_USERS_ARRAY'][$dismiss['UF_USER']] = $all_users[$dismiss['UF_USER']];
                        }
                        $now_users = count($role['NOW_USERS_ARRAY']);
                        unset($all_dismiss_by_dealer[$dealer_id][$key]);
                    }
                }
            }
            $role['COUNT_USERS_PERIOD_ARRAY'][$date] = $role['NOW_USERS_ARRAY'];
            $role['COUNT_USERS_PERIOD'][$date] = $now_users;

            $role['COMMON_VALUE_USERS_PERIOD'][$date] = round(array_sum($role['COUNT_USERS_PERIOD']) / count($role['COUNT_USERS_PERIOD']), 2);
            $role['TEK_KADR_PERIOD'][$date] = $role['COMMON_VALUE_USERS_PERIOD'][$date] > 0 ? (round(count($role['KICKED_USERS']) / $role['COMMON_VALUE_USERS_PERIOD'][$date] * 100)) : 0;
        }
        $role['START_PERIOD_USERS'] = $role['COUNT_USERS_PERIOD'][$result[count($result) - 1]];
        $role['START_PERIOD_USERS_ARRAY'] = $role['COUNT_USERS_PERIOD_ARRAY'][$result[count($result) - 1]];
        $role['END_PERIOD_USERS'] = $role['COUNT_USERS_PERIOD'][$result[0]];
        $role['END_PERIOD_USERS_ARRAY'] = $role['COUNT_USERS_PERIOD_ARRAY'][$result[0]];
        $role['COMMON_VALUE_USERS'] = round(array_sum($role['COUNT_USERS_PERIOD']) / count($role['COUNT_USERS_PERIOD']), 2);
        $role['TEK_KADR'] = $role['COMMON_VALUE_USERS'] > 0 ? (round(count($role['KICKED_USERS']) / $role['COMMON_VALUE_USERS'] * 100)) . "%" : "0%";
        $kicked_user_ids = [];
        foreach ($role['KICKED_USERS'] as $KICKED_USER) {
            $kicked_user_ids[] = $KICKED_USER['UF_USER'];
        }
        $role['COURSES_BY_KICKED_USERS'] = count((new CourseCompletion())->get(['UF_USER_ID' => array_unique($kicked_user_ids), 'UF_IS_COMPLETE' => 1, '>UF_DATE' => $from, '<UF_DATE' => $to]));
    }
    $sum_kicked_users = 0;
    $sum_sr_users = 0;
    $sr_tek = 0;
    foreach ($roles as $key_r => $sum_r) {
        $sum_kicked_users += count($sum_r['KICKED_USERS']);
        $sum_sr_users += $sum_r['COMMON_VALUE_USERS'];
    }
    if ($sum_sr_users > 0) {
        $sr_tek = ceil($sum_kicked_users / $sum_sr_users * 100);
    }
    $c_role = [];
}
$_REQUEST['report_id'] = 1;?>
<div class="main-content">
    <div class="content">
        <div class="content-block">
            <div class="text-content text-content--long">
                <h2 class="h2 center">Отчет по текучести кадров</h2>
                <div class="table-block">
                    <div class="form-group" style="display: flex; padding-top: 1rem;">
                        <div class="btn-center">
                            <a href="." class="btn">К фильтру</a>
                        </div>
                    </div>
                    <table class="table table-bordered" id="table-report" style="padding-top: 25px">
                        <thead class="thead-dark">
                            <tr>
                                <th style="vertical-align: middle" class="text-center">Город</th>
                                <th style="vertical-align: middle" class="text-center">Дилер</th>
                                <?php if($_REQUEST['direction'] == 'all') {?>
                                    <th style="vertical-align: middle" class="text-center">Регионал ОП</th>
                                    <th style="vertical-align: middle" class="text-center">Регионал ППО</th>
                                    <th style="vertical-align: middle" class="text-center">Регионал Маркетинг</th>
                                <?php } else {
                                    switch ($_REQUEST['direction']) {
                                        case 'A01':
                                            echo '<th style="vertical-align: middle" class="text-center">Регионал ППО</th>';
                                            break;
                                        case 'S01':
                                            echo '<th style="vertical-align: middle" class="text-center">Регионал ОП</th>';
                                            break;
                                        case 'M01':
                                            echo '<th style="vertical-align: middle" class="text-center">Регионал Маркетинг</th>';
                                            break;
                                    }
                                }?>
                                <th style="vertical-align: middle" class="text-center">Роль</th>
                                <th style="vertical-align: middle" class="text-center">Количество на начало периода</th>
                                <th style="vertical-align: middle" class="text-center">Новые сотрудники</th>
                                <th style="vertical-align: middle" class="text-center">Уволенные</th>
                                <th style="vertical-align: middle" class="text-center">Количество на конец периода</th>
                                <th style="vertical-align: middle" class="text-center">Среднее за период</th>
                                <th style="vertical-align: middle" class="text-center">Текучесть кадров (среднее)</th>
                                <th style="vertical-align: middle" class="text-center">Количество тренингов пройденных уволенными</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($roles as $role__){?>
                            <tr>
                                <td><?=$role__['CITY']?></td>
                                <td><?=$role__['DEALER']?></td>
                                <?php if($_REQUEST['direction'] == 'all') {?>
                                <td><?=$role__['REGIONAL_OP']?></td>
                                <td><?=$role__['REGIONAL_PPO']?></td>
                                <td><?=$role__['REGIONAL_MARKETING']?></td>
                                <?php } else {
                                    switch ($_REQUEST['direction']) {
                                        case 'A01':
                                            echo '<td style="vertical-align: middle" class="text-center">'.$role__['REGIONAL_PPO'].'</td>';
                                            break;
                                        case 'S01':
                                            echo '<td style="vertical-align: middle" class="text-center">'.$role__['REGIONAL_OP'].'</td>';
                                            break;
                                        case 'M01':
                                            echo '<td style="vertical-align: middle" class="text-center">'.$role__['REGIONAL_MARKETING'].'</td>';
                                            break;
                                    }
                                }?>
                                <td><?=$role__['ROLE']?></td>
                                <td class="load_info">
                                    <span><?=$role__['START_PERIOD_USERS']?></span>
                                    <?php if(count($role__['START_PERIOD_USERS_ARRAY'])>0){?>
                                        <span class="loaded">
                                            <?php foreach ($role__['START_PERIOD_USERS_ARRAY'] as $sp_user){?>
                                                <div style="white-space: nowrap; text-align: left"><?=$all_users[$sp_user['ID']]['NAME']?> <?=$all_users[$sp_user['ID']]['LAST_NAME']?></div>
                                            <?php }?>
                                        </span>
                                    <?php }?>
                                </td>
                                <td class="load_info">
                                    <span><?=count($role__['NEW_USERS'])?></span>
                                    <?php if(count($role__['NEW_USERS'])>0){?>
                                        <span class="loaded">
                                            <?php foreach ($role__['NEW_USERS'] as $r_new_user) {?>
                                                <div style="white-space: nowrap; text-align: left"><?=$all_users[$r_new_user['UF_USER']]['NAME']?> <?=$all_users[$r_new_user['UF_USER']]['LAST_NAME']?> (<?=$r_new_user['UF_TIME']?->format('d.m.Y') ?>)</div>
                                            <?php }?>
                                        </span>
                                    <?php }?>

                                </td>
                                <td class="load_info">
                                    <span><?=count($role__['KICKED_USERS'])?></span>
                                    <?php if(count($role__['KICKED_USERS'])>0){?>
                                        <span class="loaded">
                                            <?php foreach ($role__['KICKED_USERS'] as $r_kicked_user){?>
                                                <div style="white-space: nowrap;  text-align: left"><?=$all_users[$r_kicked_user['UF_USER']]['NAME']?> <?=$all_users[$r_kicked_user['UF_USER']]['LAST_NAME']?> (<?=$r_kicked_user['UF_TIME']?->format('d.m.Y') ?>)</div>
                                            <?php }?>
                                        </span>
                                    <?php }?>
                                </td>
                                <td class="load_info">
                                    <?php $cnt = ($role__['START_PERIOD_USERS']-count($role__['KICKED_USERS'])+count($role__['NEW_USERS']));
                                    $cnt = count($role__['END_PERIOD_USERS_ARRAY']);?>
                                    <span><?=$cnt?></span>
                                    <?php if(count($role__['END_PERIOD_USERS_ARRAY'])>0) {?>
                                        <span class="loaded">
                                            <?php foreach ($role__['END_PERIOD_USERS_ARRAY'] as $ep_user) {?>
                                                <div style="white-space: nowrap; text-align: left"><?=$all_users[$ep_user['ID']]['NAME']?> <?=$all_users[$ep_user['ID']]['LAST_NAME']?></div>
                                            <?php }?>
                                        </span>
                                    <?php }?>
                                </td>
                                <td><?=str_replace(".",",",$role__['COMMON_VALUE_USERS'])?></td>
                                <td style="white-space: nowrap"><?=$role__['TEK_KADR']?> (<?=$sr_tek?>%)</td>
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
