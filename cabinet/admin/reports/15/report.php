<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;

if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");

\Helpers\Log::write(['user' => $USER->GetID(), 'report' => '15', 'request' => $_REQUEST]);
$dealers_filter = ['ACTIVE' => 'Y'];
if(check_full_array($_REQUEST['dealer_names'])){
    $dealers_filter['ID'] = $_REQUEST['dealer_names'];
} else {
    if (check_full_array($_REQUEST['regional_op'])) {
        $dealers_filter['PROPERTY_REGIONAL'] = $_REQUEST['regional_op'];
    }
    if (check_full_array($_REQUEST['regional_ppo'])) {
        $dealers_filter['PROPERTY_REGIONAL_PPO'] = $_REQUEST['regional_ppo'];
    }
    if (check_full_array($_REQUEST['regional_marketing'])) {
        $dealers_filter['PROPERTY_REGIONAL_MARKETING'] = $_REQUEST['regional_marketing'];
    }
    if (check_full_array($_REQUEST['city'])) {
        $dealers_filter['PROPERTY_CITY'] = $_REQUEST['city'];
    }
}
$dealers = \Models\Dealer::getList($dealers_filter, ['ID', 'NAME', 'CODE', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL', "PROPERTY_CITY"]);

$regionals_ppo = [];
$regionals_op = [];
$regionals_marketing = [];
foreach ($dealers as &$dealer){
    if($dealer['PROPERTY_REGIONAL_PPO_VALUE']&&!check_full_array($regionals_ppo[$dealer['PROPERTY_REGIONAL_PPO_VALUE']])) {
        $regional_ppo_params['filter'] = ['ID' => $dealer['PROPERTY_REGIONAL_PPO_VALUE']];
        $regional_ppo_params['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $regional_ppo = current(\Models\User::getArray($regional_ppo_params));
        if($regional_ppo['ID']){
            $regionals_ppo[$regional_ppo['ID']] = $dealer['REGIONAL_PPO'] = $regional_ppo['LAST_NAME']." ".$regional_ppo['NAME'];
        }
    }
    if($dealer['PROPERTY_REGIONAL_VALUE']&&!check_full_array($regionals_op[$dealer['PROPERTY_REGIONAL_VALUE']])) {
        $regional_op_params['filter'] = ['ID' => $dealer['PROPERTY_REGIONAL_VALUE']];
        $regional_op_params['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $regional_op = current(\Models\User::getArray($regional_op_params));
        if($regional_op['ID']){
            $regionals_op[$regional_op['ID']] = $dealer['REGIONAL_OP'] = $regional_op['LAST_NAME']." ".$regional_op['NAME'];
        }
    }
    if($dealer['PROPERTY_REGIONAL_MARKETING_VALUE']&&!check_full_array($regionals_marketing[$dealer['PROPERTY_REGIONAL_MARKETING_VALUE']])) {
        $regional_marketing_params['filter'] = ['ID' => $dealer['PROPERTY_REGIONAL_MARKETING_VALUE']];
        $regional_marketing_params['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $regional_marketing = current(\Models\User::getArray($regional_marketing_params));
        if($regional_marketing['ID']){
            $regionals_marketing[$regional_marketing['ID']] = $dealer['REGIONAL_MARKETING'] = $regional_marketing['LAST_NAME']." ".$regional_marketing['NAME'];
        }
    }
}
if(check_full_array($_REQUEST['role'])){
    $roles = \Teaching\Roles::getRolesList(['ID' => $_REQUEST['role'], 'ACTIVE' => 'Y']);
} else {
    $roles = [];
    switch ($_REQUEST['direction']) {
        case 'A01':
            $roles = \Teaching\Roles::getPPORoles();
            break;
        case 'M01':
            $roles = \Teaching\Roles::getMarketingRoles();
            break;
        case 'S01':
            $roles = \Teaching\Roles::getOPRoles();
            break;
        case 'all':
            $roles = \Teaching\Roles::getAll();
    }
}
$data = [];
$params['filter'] = [
    'ACTIVE' => 'Y',
    '!UF_DEALER' => false,
    "!UF_ROLE" => false
];
$params['select'] = ['ID', 'UF_CERT_USER', "UF_DEALER", "UF_ROLE", "UF_CERT_USER_DATA"];
$users = \Models\User::getArray($params);
foreach ($dealers as $dealer_){
    if($dealer_["ID"]==360)
        continue;
    foreach ($roles as $role_id => $role){
        $data[$dealer_['ID']."_".$role_id]['USERS'] = [];
        $data[$dealer_['ID']."_".$role_id]['CERT_USERS'] = 0;
        foreach ($users as $key => $user){
            if($user['UF_DEALER']!=$dealer_['ID'])
                continue;
            if(!in_array($role_id, $user['UF_ROLE']))
                continue;
            $data[$dealer_['ID']."_".$role_id]['USERS'][] = $user;
            if($user['UF_CERT_USER']==1) {
                if (!empty($_REQUEST['course_date_before']) || !empty($_REQUEST['course_date_after'])){
                    if (empty($_REQUEST['course_date_before']))
                        $start_request_tmstmp = strtotime("01.01.1970");
                    else
                        $start_request_tmstmp = strtotime($_REQUEST['course_date_before']);
                    if (empty($_REQUEST['course_date_after']))
                        $end_request_tmstmp = time();
                    else
                        $end_request_tmstmp = strtotime($_REQUEST['course_date_after']);

                    $date_cert_tmstmp = strtotime($user['UF_CERT_USER_DATA']);
                    if ($date_cert_tmstmp >= $start_request_tmstmp && $date_cert_tmstmp <= $end_request_tmstmp ) {
                        $data[$dealer_['ID'] . "_" . $role_id]['CERT_USERS']++;
                    }

                } else {
                    $data[$dealer_['ID'] . "_" . $role_id]['CERT_USERS']++;
                }
            }
            unset($users[$key]);
        }
        $data[$dealer_['ID']."_".$role_id]['DEALER'] = $dealer_;
        $data[$dealer_['ID']."_".$role_id]['ROLE'] = ["ID" => $role_id, 'NAME' => $role];

        if(count($data[$dealer_['ID']."_".$role_id]['USERS'])==0)
            unset($data[$dealer_['ID']."_".$role_id]);
        else
            $data[$dealer_['ID']."_".$role_id]['PERCENT'] = round($data[$dealer_['ID']."_".$role_id]['CERT_USERS']/count($data[$dealer_['ID']."_".$role_id]['USERS']), 2)*100;
    }
}
usort($data, function($a, $b){
    return ($b['PERCENT'] - $a['PERCENT']);
});
$_REQUEST['report_id'] = 1; ?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Рейтинг дилеров по % аттестованного персонала</h2>
                <div class="text-content text-content--long">
                    <div class="table-block">
                        <div class="form-group" style="display: flex; padding-top: 1rem;">
                            <div class="btn-center">
                                <a href="." class="btn">К генератору</a>
                            </div>
                            <div class="btn-center">
                                <button class="btn" id="gen"><span>Excel</span></button>
                            </div>
                        </div>
                        <table class="table table-bordered" id="table-report" style="padding-top: 25px">
                            <thead class="thead-dark">
                            <tr>
                                <th style="vertical-align: middle" class="text-center"></th>
                                <th style="vertical-align: middle" class="text-center">Код дилера</th>
                                <th style="vertical-align: middle" class="text-center">Название ДЦ</th>
                                <th style="vertical-align: middle" class="text-center">Город</th>
                                <?php if($_REQUEST['direction'] == 'all'){?>
                                    <th style="vertical-align: middle" class="text-center">Регионал ОП</th>
                                    <th style="vertical-align: middle" class="text-center">Регионал ППО</th>
                                    <th style="vertical-align: middle" class="text-center">Регионал Маркетинг</th>
                                <?php } else {
                                    switch ($_REQUEST['direction']){
                                        case 'S01';
                                            echo '<th style="vertical-align: middle" class="text-center">Регионал ОП</th>';
                                            break;
                                        case 'A01';
                                            echo '<th style="vertical-align: middle" class="text-center">Регионал ППО</th>';
                                            break;
                                        case 'M01';
                                            echo '<th style="vertical-align: middle" class="text-center">Регионал Маркетинг</th>';
                                            break;
                                    }
                                }?>
                                <th style="vertical-align: middle" class="text-center">Роль</th>
                                <th style="vertical-align: middle" class="text-center">Всего сотрудников по роли</th>
                                <th style="vertical-align: middle" class="text-center">Количество аттестованных</th>
                                <th style="vertical-align: middle" class="text-center">% аттестованных</th>
                            </tr>

                            </thead>
                            <tbody>
                            <?php foreach ($data as $key => $item){?>
                                <tr>
                                    <td><?=($key+1)?></td>
                                    <td><?=$item['DEALER']['CODE']?></td>
                                    <td><?=$item['DEALER']['NAME']?></td>
                                    <td><?=$item['DEALER']['PROPERTY_CITY_VALUE']?></td>
                                    <?php if($_REQUEST['direction'] == 'all'){?>
                                        <td><?=$item['DEALER']['REGIONAL_OP']?></td>
                                        <td><?=$item['DEALER']['REGIONAL_PPO']?></td>
                                        <td><?=$item['DEALER']['REGIONAL_MARKETING']?></td>
                                    <?php } else {
                                        switch ($_REQUEST['direction']){
                                            case 'S01';
                                                echo '<td>'.$item['DEALER']['REGIONAL_OP'].'</td>';
                                                break;
                                            case 'A01';
                                                echo '<td>'.$item['DEALER']['REGIONAL_PPO'].'</td>';
                                                break;
                                            case 'M01';
                                                echo '<td>'.$item['DEALER']['REGIONAL_MARKETING'].'</td>';
                                                break;
                                        }
                                    }?>

                                    <td><?=$item['ROLE']['NAME']?></td>
                                    <td><?=count($item['USERS'])?></td>
                                    <td><?=$item['CERT_USERS']?></td>
                                    <td><?=$item['PERCENT']?> %</td>
                                </tr>
                            <?php }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>