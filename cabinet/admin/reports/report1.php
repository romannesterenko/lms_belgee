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
    $dealer = current(DealerHelper::getList(['>ID' => 0], ['ID', 'NAME', 'CODE', 'PROPERTY_CITY']));
    $dealer_id = $dealer['ID'];
} else {
    $dealer = current(DealerHelper::getList(['ID' => $dealer_id], ['ID', 'NAME', 'CODE', 'PROPERTY_CITY']));
}
$employees = Employee::getListByDealer($dealer['ID'], ['ID', 'UF_ROLE', 'NAME', 'LAST_NAME']);
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
            $completion = current((new CourseCompletion())->get(['UF_COURSE_ID' => $course['ID'], 'UF_USER_ID' => $user['ID'], 'UF_IS_COMPLETE' => 1]));
            if(check_full_array($completion)&&$completion['ID']>0) {
                $course['COMPLETED']++;
                $course['COMPLETION_INFO'][] = [
                        "USER_NAME" => $user['NAME']." ".$user['LAST_NAME'],
                        "DATE" => $completion['UF_DATE'],
                ];
            }
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
                        <tbody>
                        <tr style="height: 50px">
                            <td class="text-left" colspan="2"><?= Loc::getMessage('CITY') ?></td>
                            <td class="text-left"><?=$dealer['PROPERTY_CITY_VALUE']?></td>
                        </tr>
                        <tr style="height: 50px">
                            <td class="text-left" colspan="2">Код дилера</td>
                            <td class="text-left"><?=$dealer['CODE']?></td>
                        </tr>
                        <tr style="height: 50px">
                            <td class="text-left" colspan="2"><?= Loc::getMessage('DEALER_NAME') ?></td>
                            <td class="text-left"><?=$dealer['NAME']?></td>
                        </tr>
                        <tr style="height: 50px">
                            <td class="text-left" colspan="2"><?= Loc::getMessage('REGIONAL_OP') ?></td>
                            <td class="text-left"><?=$dealer['REGIONAL_OP']?></td>
                        </tr>
                        <tr style="height: 50px">
                            <td class="text-left" colspan="2"><?= Loc::getMessage('REGIONAL_PPO') ?></td>
                            <td class="text-left"><?=$dealer['REGIONAL_PPO']?></td>
                        </tr>
                        <?php foreach ($dealer['ROLES_LIST'] as $role){
                            $rcnt = 0;
                            foreach ($role['COURSES'] as $course){
                                if($rcnt==0){?>
                                    <tr style="height: 50px">
                                        <td class="text-left" rowspan="<?=count($role['COURSES'])+1?>"><b><?= Loc::getMessage('ROLE_FOR_COURSE') ?> "<?=$role['NAME']?>"</b></td>
                                        <td class="text-left" style="padding-left:20px"><b><?= Loc::getMessage('EMPLOYESS') ?></b></td>
                                        <td class="text-left"><?=count($role['USERS'])?></td>
                                    </tr>
                                <?php }?>
                                <tr style="height: 50px">
                                    <td class="text-left"><?=$course['NAME']?></td>
                                    <td class="text-left<?=check_full_array($course['COMPLETION_INFO'])?' load_info':''?>" style="width: 40%; height: 60px">
                                        <span><?=$course['COMPLETED']?> (<?=$course['COMPLETED_PERCENTS']?>)</span>
                                        <?php if(check_full_array($course['COMPLETION_INFO'])){?>
                                            <span class="loaded">
                                                <?php foreach ($course['COMPLETION_INFO'] as $completion_info){?>
                                                    <div><?=$completion_info['USER_NAME']?> (<?=$completion_info['DATE']?>)</div>
                                                <?php }?>
                                            </span>
                                        <?php }?>
                                    </td>
                                </tr>
                                <?php
                                $rcnt++;
                            }?>
                            <?php

                        }?>
                        </tbody>
                    </table>

                    <button class="dt-button buttons-pdf buttons-html5" id="gen"><span>Excel</span></button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
