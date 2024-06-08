<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
$dealers = \Models\Dealer::getAll();
$regionals_ppo = [];
$regionals_op = [];
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
}
$roles = \Teaching\Roles::getPPORoles();
if($_REQUEST['op_servis_op']=='on'){
    $roles = \Teaching\Roles::getOPRoles();
}
$params['filter']['!UF_DEALER'] = false;
$params['filter']['UF_ROLE'] = array_keys($roles);
if(check_full_array($_REQUEST['dealer_codes'])){
    $params['filter']['UF_DEALER'] = $_REQUEST['dealer_codes'];
}
$params['select'] = ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEALER', 'UF_ROLE', 'UF_CERT_USER'];
$users = \Models\User::getArray($params);
$_REQUEST['report_id'] = 1; ?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Штатное расписание</h2>
                <div class="text-content text-content--long">
                    <div class="form-group" style="display: flex; padding-top: 1rem;">
                        <div class="btn-center">
                            <a href="report12_prefilter.php" class="btn">К генератору</a>
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
                                <th style="vertical-align: middle" class="text-center">Региональный менеджер</th>
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
                                    <td><?=$_REQUEST['op_servis_op']=='on'?$dealers[$user['UF_DEALER']]['REGIONAL_OP']:$dealers[$user['UF_DEALER']]['REGIONAL_PPO']?></td>
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