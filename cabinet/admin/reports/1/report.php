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
if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");
$settings = Reports::getByCode('courses_for_dealer');
$dealer_id = (int)($_REQUEST['dealer_codes']??UserHelper::getDealerId());
\Helpers\Log::write(['user' => $USER->GetID(), 'report' => '1', 'request' => $_REQUEST]);
$_REQUEST['report_id'] = $dealer_id;
if(!$dealer_id>0) {
    $dealer = current(DealerHelper::getList(['>ID' => 0], ['ID', 'NAME', 'CODE', 'PROPERTY_CITY']));
    $dealer_id = $dealer['ID'];
} else {
    $dealer = current(DealerHelper::getList(['ID' => $dealer_id], ['ID', 'NAME', 'CODE', 'PROPERTY_CITY']));
}
$employees = Employee::getListByDealer($dealer['ID'], ['ID', 'UF_ROLE', 'NAME', 'LAST_NAME', 'DATE_REGISTER', 'UF_REQUIRED_COURSES']);

$roles = [];
$completions = new CourseCompletion();
foreach($employees as $employee) {
    //echo $employee['DATE_REGISTER']->format('d.m.Y')."<br/>";
    if(is_array($employee['UF_ROLE']))
        $roles = array_merge($roles, $employee['UF_ROLE']);
}

$dealer['ROLES_LIST'] = Roles::getRolesList(['ID' => array_unique($roles)], ['ID', 'NAME']);

//$courses = Courses::getNeededByRoles(array_keys($dealer['ROLES_LIST']), ['ID', 'NAME']);

foreach ($dealer['ROLES_LIST'] as &$role){
    $role['COURSES'] = Courses::getNeededByRoles($role['ID'], ['ID', 'NAME']);
    $role['SETTED_COURSES'] = [];
    foreach ($employees as $employee) {
        if (is_array($employee['UF_ROLE'])&&in_array($role['ID'], $employee['UF_ROLE'])) {
            $employee['UF_REQUIRED_COURSES'] = check_full_array($employee['UF_REQUIRED_COURSES'])?$employee['UF_REQUIRED_COURSES']:[];
            foreach ($employee['UF_REQUIRED_COURSES'] as $key => $req_course_id){
                if(check_full_array($role['COURSES'][$req_course_id]))
                    unset($employee['UF_REQUIRED_COURSES'][$key]);
            }
            /*if (check_full_array($employee['UF_REQUIRED_COURSES'])) {
                foreach (Courses::getList(['ID' => $employee['UF_REQUIRED_COURSES']], ['ID', 'NAME']) as $required_course) {
                    $role['SETTED_COURSES'][$required_course['ID']][] = $employee;
                    $role['COURSES'][$required_course['ID']] = $required_course;
                }
            }*/
            $role['USERS'][] = $employee;
        }
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
        $count_users = count($role['USERS']);
        if(check_full_array($role['SETTED_COURSES'][$course['ID']])){
            $count_users = count($role['SETTED_COURSES'][$course['ID']]);
        }
        $course['COMPLETED_PERCENTS'] = $course['COMPLETED']==0?'0%':floor($course['COMPLETED']/$count_users*100).'%';
    }
}

$dealer['EMPLOYESS_CNT'] = count($employees);
$dealer['REGIONAL_OP'] = Dealer::getRegionalOP($dealer_id);
$dealer['REGIONAL_PPO'] = Dealer::getRegionalPPO($dealer_id);
//dumpdie($dealer);
?>
<div class="main-content">
    <div class="content">
        <div class="content-block">
            <input type="hidden" id="is_adaptive" value="<?=$settings['PROPERTIES']['IS_ADAPTIVE']?>">
            <div class="text-content text-content--long">
                <h2 class="h2 center"><?= Loc::getMessage('TITLE')?> "<?=$dealer['NAME']?>", <?=$dealer['PROPERTY_CITY_VALUE']?></h2>
                <div class="form-group" style="display: flex; padding-top: 1rem;">
                    <div class="btn-center">
                        <a href="." class="btn">К фильтру</a>
                    </div>
                </div>
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
                        <?php foreach ($dealer['ROLES_LIST'] as $role__){
                            $rcnt = 0;
                            foreach ($role__['COURSES'] as $course__){
                                if($rcnt==0){?>
                                    <tr style="height: 50px">
                                        <td class="text-left" rowspan="<?=count($role__['COURSES'])+1?>"><b><?= Loc::getMessage('ROLE_FOR_COURSE') ?> "<?=$role__['NAME']?>"</b></td>
                                        <td class="text-left" style="padding-left:20px"><b><?= Loc::getMessage('EMPLOYESS') ?></b></td>
                                        <td class="text-left">
                                            <b><?=count($role__['USERS'])?></b>
                                            <?php foreach ($role__['USERS'] as $role_user){
                                                echo "<br/>".$role_user['NAME']." ".$role_user['LAST_NAME']." (".$role_user['DATE_REGISTER']->format('d.m.Y').")";
                                            }?>
                                        </td>
                                    </tr>
                                <?php }?>
                                <tr style="height: 50px">
                                    <td class="text-left"><?=$course__['NAME']?></td>
                                    <td class="text-left<?//=check_full_array($course__['COMPLETION_INFO'])?' load_info':''?>" style="width: 40%; height: 60px">
                                        <span><?=$course__['COMPLETED']?> (<?=$course__['COMPLETED_PERCENTS']?>)</span>
                                        <?php if(check_full_array($course__['COMPLETION_INFO'])){?>
                                            <span class="">
                                                <?php foreach ($course__['COMPLETION_INFO'] as $completion_info){?>
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
