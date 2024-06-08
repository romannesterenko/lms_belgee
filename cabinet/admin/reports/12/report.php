<?php

use Helpers\Log;
use Models\Dealer;
use Models\User;
use Teaching\Roles;

const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;

if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");

Log::write(['user' => $USER->GetID(), 'report' => '12', 'request' => $_REQUEST]);
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
    if (check_full_array($_REQUEST['city'])) {
        $dealers_filter['PROPERTY_CITY'] = $_REQUEST['city'];
    }
}
$dealers = Dealer::getList($dealers_filter, ['ID', 'NAME', 'CODE', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL', "PROPERTY_CITY"]);
$new_dealers = [];
foreach ($dealers as $dealer){
    $new_dealers[$dealer['ID']] = $dealer;
}
$dealers = $new_dealers;
$regionals_ppo = [];
$regionals_op = [];
foreach ($dealers as &$dealer){
    if($dealer['PROPERTY_REGIONAL_PPO_VALUE']&&!check_full_array($regionals_ppo[$dealer['PROPERTY_REGIONAL_PPO_VALUE']])) {
        $regional_ppo_params['filter'] = ['ID' => $dealer['PROPERTY_REGIONAL_PPO_VALUE']];
        $regional_ppo_params['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $regional_ppo = current(User::getArray($regional_ppo_params));
        if($regional_ppo['ID']){
            $regionals_ppo[$regional_ppo['ID']] = $dealer['REGIONAL_PPO'] = $regional_ppo['LAST_NAME']." ".$regional_ppo['NAME'];
        }
    }
    if($dealer['PROPERTY_REGIONAL_VALUE']&&!check_full_array($regionals_op[$dealer['PROPERTY_REGIONAL_VALUE']])) {
        $regional_op_params['filter'] = ['ID' => $dealer['PROPERTY_REGIONAL_VALUE']];
        $regional_op_params['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'];
        $regional_op = current(User::getArray($regional_op_params));
        if($regional_op['ID']){
            $regionals_op[$regional_op['ID']] = $dealer['REGIONAL_OP'] = $regional_op['LAST_NAME']." ".$regional_op['NAME'];
        }
    }
}
$roles = [];
if(check_full_array($_REQUEST['role'])){
    $roles = Roles::getRolesList(['ID' => $_REQUEST['role'], 'ACTIVE' => 'Y']);
} else {
    switch ($_REQUEST['direction']) {
        case 'A01':
            $roles = Roles::getPPORoles();
            break;
        case 'S01':
            $roles = Roles::getOPRoles();
            break;
        case 'M01':
            $roles = Roles::getMarketingRoles();
            break;
        case 'all':
            $roles = Roles::getAll();
    }
}
$params['filter']['!UF_DEALER'] = false;
$params['filter']['UF_ROLE'] = array_keys($roles);
if(check_full_array($_REQUEST['dealer_codes'])){
    $params['filter']['UF_DEALER'] = $_REQUEST['dealer_codes'];
} else {
    if(check_full_array($dealers)){
        $params['filter']['UF_DEALER'] = array_keys($dealers);
    }
}
$params['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEALER', 'UF_ROLE', 'UF_CERT_USER'];
$users = User::getArray($params);
$_REQUEST['report_id'] = 1; ?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Штатное расписание</h2>
                <div class="text-content text-content--long">
                    <div class="form-group" style="display: flex; padding-top: 1rem;">
                        <div class="btn-center">
                            <a href="." class="btn">К генератору</a>
                        </div>
                        <div class="btn-center">
                            <button class="btn" id="gen"><span>Excel</span></button>
                        </div>
                    </div>
                    <div class="table-block">
                        <table class="table table-bordered" id="table-report" style="padding-top: 25px">
                            <thead class="thead-dark">
                            <tr>
                                <th style="vertical-align: middle" class="text-center"></th>
                                <th style="vertical-align: middle" class="text-center">Дата отчета</th>
                                <th style="vertical-align: middle" class="text-center">Дилерский центр</th>
                                <?php
                                if ($_REQUEST['direction']=='all'){?>
                                    <th>Регионал ОП</th>
                                    <th>Регионал ППО</th>
                                    <th>Регионал Маркетинг</th>
                                <?php } else {
                                    switch ($_REQUEST['direction']) {
                                        case "S01":
                                            echo "<th>Регионал ОП</th>";
                                            break;
                                        case "A01":
                                            echo "<th>Регионал ППО</th>";
                                            break;
                                        case "M01":
                                            echo "<th>Регионал Маркетинг</th>";
                                            break;
                                    }
                                }?>
                                <th style="vertical-align: middle" class="text-center">ФИО</th>
                                <th style="vertical-align: middle" class="text-center">Роль</th>
                                <th style="vertical-align: middle" class="text-center">Сертифицированный сотрудник</th>
                            </tr>

                            </thead>
                            <tbody>
                            <?php foreach ($users as $key => $user){?>
                                <tr>
                                    <td><?=($key+1)?></td>
                                    <td><?=date('d.m.Y')?></td>
                                    <td><?=$dealers[$user['UF_DEALER']]['NAME']?></td>
                                    <?php
                                    if ($_REQUEST['direction']=='all') {?>
                                        <td><?=Dealer::getRegionalOP($user['UF_DEALER'])?></td>
                                        <td><?=Dealer::getRegionalPPO($user['UF_DEALER'])?></td>
                                        <td><?=Dealer::getRegionalMarketing($user['UF_DEALER'])?></td>
                                    <?php } else {
                                        switch ($_REQUEST['direction']) {
                                            case "S01":
                                                echo "<td>".Dealer::getRegionalOP($user['UF_DEALER'])."</td>";
                                                break;
                                            case "A01":
                                                echo "<td>".Dealer::getRegionalPPO($user['UF_DEALER'])."</td>";
                                                break;
                                            case "M01":
                                                echo "<td>".Dealer::getRegionalMarketing($user['UF_DEALER'])."</td>";
                                                break;
                                        }
                                    }?>
                                    <td><?=$user['NAME']?> <?=$user['LAST_NAME']?></td>
                                    <td>
                                        <?php foreach ($user['UF_ROLE'] as $role_id){
                                            if($roles[$role_id])
                                                echo $roles[$role_id]."<br />";
                                        }?>
                                    </td>
                                    <td><?=$user['UF_CERT_USER']==1?'Да':'Нет'?></td>
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