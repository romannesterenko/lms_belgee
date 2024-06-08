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

global $APPLICATION;
$settings = Reports::getByCode('courses_for_dealer');
$dealer_id = (int)($_REQUEST['dealer_id']??UserHelper::getDealerId());
$_REQUEST['report_id'] = $dealer_id;
if(!$dealer_id>0) {
    $dealer = current(DealerHelper::getList(['>ID' => 0], ['ID', 'NAME', 'PROPERTY_CITY']));
    $dealer_id = $dealer['ID'];
} else {
    $dealer = current(DealerHelper::getList(['ID' => $dealer_id], ['ID', 'NAME', 'PROPERTY_CITY']));
}
$employees = Employee::getListByDealer($dealer['ID'], ['ID', 'UF_ROLE']);
$roles = [];
$completions = new CourseCompletion();
foreach($employees as $employee) {
    if(is_array($employee['UF_ROLE']))
        $roles = array_merge($roles, $employee['UF_ROLE']);
}
$dealer['ROLES_LIST'] = Roles::getRolesList(['ID' => array_unique($roles)], ['ID', 'NAME']);

$courses = Courses::getByRole(array_keys($dealer['ROLES_LIST']), ['ID', 'NAME']);
foreach ($dealer['ROLES_LIST'] as &$role){
    $role['COURSES'] = Courses::getByRole($role['ID'], ['ID', 'NAME']);

    foreach ($employees as $employee) {
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

$dealer['EMPLOYESS_CNT'] = count($employees);
$dealer['REGIONAL_OP'] = Dealer::getRegionalOP($dealer_id);
$dealer['REGIONAL_PPO'] = Dealer::getRegionalPPO($dealer_id);
?>
<div class="main-content">
    <aside>
        <div class="aside-sticky aside-sticky--title">
            <?php require_once(PageHelper::getSideBarForCabinet());?>
        </div>
    </aside>
    <div class="content">
        <div class="content-block">
            <input type="hidden" id="is_adaptive" value="<?=$settings['PROPERTIES']['IS_ADAPTIVE']?>">
            <div class="text-content text-content--long">
                <h2 class="h2 center"><?= Loc::getMessage('TITLE')?> "<?=$dealer['NAME']?>", <?=$dealer['PROPERTY_CITY_VALUE']?></h2>
                <form style="display: flex; justify-content: space-between">
                    <div class="form-group" style="display: flex;">
                        <div class="form-group">
                            <label for=""><?= Loc::getMessage('DEALER_FILTER') ?></label>
                            <div class="select">
                                <select class="select2" name="dealer_id">
                                    <?php foreach (DealerHelper::getList(['ACTIVE' => 'Y'], ['ID', 'NAME']) as $item) {?>
                                        <option value="<?=$item['ID']?>"<?=$item['ID']==$dealer_id?' selected':'';?>><?=$item['NAME']?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" style="display: flex; padding-top: 1rem;">
                        <div class="btn-center">
                            <button class="btn"><?= Loc::getMessage('SUBMIT') ?></button>
                        </div>
                        <div class="btn-center">
                            <a role="button" href="<?=$APPLICATION->GetCurPage()?>" class="btn"><?= Loc::getMessage('RESET') ?></a>
                        </div>
                    </div>
                </form>
                <div class="table-block">
                    <table class="table table-bordered table-striped table--white" id="table-report" style="padding-top: 25px">
                        <thead class="thead-dark">
                        <tr>
                            <th rowspan="2" style="vertical-align: middle;"><?= Loc::getMessage('CITY') ?></th>
                            <th rowspan="2" style="vertical-align: middle;"><?= Loc::getMessage('DEALER_NAME') ?></th>
                            <th rowspan="2" style="vertical-align: middle;"><?= Loc::getMessage('REGIONAL_OP') ?></th>
                            <th rowspan="2" style="vertical-align: middle;"><?= Loc::getMessage('REGIONAL_PPO') ?></th>
                            <?php unset($role);
                            foreach ($dealer['ROLES_LIST'] as $role){?>
                                <th colspan="<?=count($role['COURSES'])+1?>" class="text-center" style="vertical-align: middle;"><b><?= Loc::getMessage('ROLE_FOR_COURSE') ?> "<?=$role['NAME']?>"</b></th>
                            <?php }?>
                        </tr>
                        <tr>
                            <?php foreach ($dealer['ROLES_LIST'] as $role){?>
                                <th style="vertical-align: middle; text-align: center"><?= Loc::getMessage('EMPLOYESS') ?></th>
                                <?php foreach ($role['COURSES'] as $course){?>
                                    <th  style="vertical-align: middle;"><?=$course['NAME']?></th>
                                <?php }?>
                            <?php }?>
                        </tr>
                        </thead>
                        <tbody>
                        <tr style="height: 50px">
                            <td class="text-left"><?=$dealer['PROPERTY_CITY_VALUE']?></td>
                            <td class="text-left"><?=$dealer['NAME']?></td>
                            <td class="text-left"><?=$dealer['REGIONAL_OP']?></td>
                            <td class="text-left"><?=$dealer['REGIONAL_PPO']?></td>
                            <?php foreach ($dealer['ROLES_LIST'] as $role){?>
                                <td><b><?=count($role['USERS'])?></b></td>
                                <?php foreach ($role['COURSES'] as $course){?>
                                    <td><?=$course['COMPLETED']?> (<?=$course['COMPLETED_PERCENTS']?>)</td>
                                <?php }?>
                            <?php }?>
                        </tr>
                        </tbody>
                    </table>

                    <button class="dt-button buttons-pdf buttons-html5" id="gen"><span>Excel</span></button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
