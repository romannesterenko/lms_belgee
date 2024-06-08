<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
use Bitrix\Main\Localization\Loc;
use Helpers\DealerHelper;
use Helpers\PageHelper;
use Helpers\UserHelper;
use Models\Dealer;
use Models\Employee;
use Settings\Reports;
use Teaching\CourseCompletion;
use Teaching\Courses;
use Teaching\Roles;
if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");
global $APPLICATION, $USER;
$dealer_id = (int)($_REQUEST['dealer_codes']??UserHelper::getDealerId());
\Helpers\Log::write(['user' => $USER->GetID(), 'report' => '1', 'request' => $_REQUEST]);
$_REQUEST['report_id'] = $dealer_id;
//$user_filter = [];
$user_filter['!UF_DEALER'] = false;
if(check_full_array($_REQUEST['regional_ppo'])){
    $dealers = \Models\Dealer::getByRegionalPPO($_REQUEST['regional_ppo']);
    $user_filter['UF_DEALER'] = array_merge((array)$user_filter['UF_DEALER']??[], array_keys($dealers));
}
if(check_full_array($_REQUEST['regional_op'])){
    $dealers = \Models\Dealer::getByRegionalOP($_REQUEST['regional_op']);
    $user_filter['UF_DEALER'] = array_merge((array)$user_filter['UF_DEALER']??[], array_keys($dealers));
}
if(check_full_array($_REQUEST['regional_marketing'])){
    $dealers = \Models\Dealer::getByRegionalMarketing($_REQUEST['regional_marketing']);
    $user_filter['UF_DEALER'] = array_merge((array)$user_filter['UF_DEALER']??[], array_keys($dealers));
}
if(check_full_array($_REQUEST['dealer_codes'])){
    $user_filter['UF_DEALER'] = $_REQUEST['dealer_codes'];
}
if(check_full_array($_REQUEST['role'])){
    $user_filter['UF_ROLE'] = $_REQUEST['role'];
}
if(check_full_array($_REQUEST['employee_level'])){
    $user_filter['UF_USER_RATING'] = $_REQUEST['employee_level'];
}
$employees = \Models\User::get($user_filter, ['ID', 'UF_DEALER', 'UF_ROLE', 'NAME', 'LAST_NAME', 'UF_REQUIRED_COURSES', 'UF_USER_RATING']);
$dealer_ids = [];
foreach ($employees as $employee){
    $dealer_ids[] = $employee['UF_DEALER'];
}
$dealers = Dealer::getList(['ID' => array_values(array_unique($dealer_ids))], ['ID', 'CODE', 'NAME']);
foreach ($dealers as &$dealer){
    $dealer['REGIONAL_OP'] = Dealer::getRegionalOP($dealer['ID']);
    $dealer['REGIONAL_PPO'] = Dealer::getRegionalPPO($dealer['ID']);
    $dealer['REGIONAL_MARKETING'] = Dealer::getRegionalMarketing($dealer['ID']);
}
$role_ids = [];
foreach($employees as $employee) {
    if(is_array($employee['UF_ROLE']))
        $role_ids = array_merge($role_ids, $employee['UF_ROLE']);
}
$roles = \Models\Role::getList(['ID' => array_values(array_unique($role_ids))], ['ID', 'NAME']);
foreach($employees as &$one_employee) {
    $role_names = [];
    foreach ($one_employee['UF_ROLE'] as $one_role)
        $role_names[] = $roles[$one_role]['NAME'];
    $one_employee['PRINT_ROLES'] = implode('<br/>', $role_names);
}
$level_list = \Models\User::getLevelList();
?>
<div class="main-content">
    <div class="content">
        <div class="content-block">
            <div class="text-content text-content--long">
                <h2 class="h2 center"><?= Loc::getMessage('TITLE')?></h2>
                <div class="form-group" style="display: flex; padding-top: 1rem;">
                    <div class="btn-center">
                        <a href="." class="btn">К фильтру</a>
                    </div>
                </div>
                <div class="table-block">
                    <table class="table table-bordered table-striped table--white" id="table-report" style="padding-top: 25px">
                        <thead>
                            <tr>
                                <th>ФИО</th>
                                <th>Код дилера</th>
                                <th>Наименование дилера</th>
                                <th>Регионал ОП</th>
                                <th>Регионал ППО</th>
                                <th>Регионал Маркетинг</th>
                                <th>Роль</th>
                                <th class="text-center">Уровень</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $employee){?>
                                <tr>
                                    <td class="text-left"><?=$employee['NAME']?> <?=$employee['LAST_NAME']?></td>
                                    <td class="text-left"><?=$dealers[$employee['UF_DEALER']]['CODE']?></td>
                                    <td class="text-left"><?=$dealers[$employee['UF_DEALER']]['NAME']?></td>
                                    <td class="text-left"><?=$dealers[$employee['UF_DEALER']]['REGIONAL_OP']?></td>
                                    <td class="text-left"><?=$dealers[$employee['UF_DEALER']]['REGIONAL_PPO']?></td>
                                    <td class="text-left"><?=$dealers[$employee['UF_DEALER']]['REGIONAL_MARKETING']?></td>
                                    <td class="text-left"><?=$employee['PRINT_ROLES']?></td>
                                    <td class="text-center"><?=(int)$employee['UF_USER_RATING']>0?$level_list[(int)$employee['UF_USER_RATING']]:0?></td>
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
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
