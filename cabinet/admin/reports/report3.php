<?php

use Bitrix\Main\Localization\Loc;

const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER;
$dealer_id = (int)$_REQUEST['dealer_id']>0?(int)$_REQUEST['dealer_id']:\Helpers\UserHelper::getDealerId();
$_REQUEST['report_id'] = $dealer_id;
$rows = [];
$select = \Teaching\Courses::getDirectionsList();
$select[] = [
    'UF_NAME' => 'OTHERS',
    'UF_XML_ID' => false,
];
$settings = \Settings\Reports::getRolesForDealerReport();
$dealer = current(\Helpers\DealerHelper::getList(['ID'=>(int)$_REQUEST['report_id']], ['ID', 'NAME', 'PROPERTY_CITY']));
$employees = \Models\Employee::getListByDealer($dealer['ID'], ['ID', 'UF_ROLE']);
$roles = [];
$completions = new \Teaching\CourseCompletion();
foreach($employees as $employee) {
    if(is_array($employee['UF_ROLE']))
        $roles = array_merge($roles, $employee['UF_ROLE']);
}
$dealer['ROLES_LIST'] = \Teaching\Roles::getRolesList(['ID' => array_unique($roles)], ['ID', 'NAME']);
$courses = \Teaching\Courses::getByRole(array_keys($dealer['ROLES_LIST']), ['ID', 'NAME', 'PROPERTY_COURSE_CATEGORY']);
foreach ($courses as $key=>$cr){
    if(!empty($_REQUEST['dir'])&&$_REQUEST['dir']!=0){
        if(strtolower($_REQUEST['dir'])=='others'){
            if($cr['PROPERTY_COURSE_CATEGORY_VALUE']!=''){
                unset($courses[$key]);
            }
        } elseif(strtolower($_REQUEST['dir'])!=strtolower($cr['PROPERTY_COURSE_CATEGORY_VALUE'])){
            unset($courses[$key]);
        }
    }
}
foreach ($dealer['ROLES_LIST'] as &$role){
    $role['COURSES'] = \Teaching\Courses::getByRole($role['ID'], ['ID', 'NAME']);
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
$total_users = 0;
?>
<div class="main-content">
    <aside>
        <div class="aside-sticky aside-sticky--title">
            <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
        </div>
    </aside>
    <div class="content">
        <div class="content-block">
            <input type="hidden" id="is_adaptive" value="<?=$settings['PROPERTIES']['IS_ADAPTIVE']?>">
                <div class="text-content text-content--long">
                    <h2 class="h2 center"><?= Loc::getMessage('TITLE') ?>"<?=$dealer['NAME']?>", <?=$dealer['PROPERTY_CITY_VALUE']?></h2>
                    <form style="display: flex; justify-content: space-between">
                        <div class="form-group" style="display: flex;">
                            <div class="form-group mr-20">
                                <label for=""><?= Loc::getMessage('CATEGORY') ?></label>
                                <div class="select">
                                    <select class="select2" name="dir">
                                        <option value="0" style="padding-left: 5px"><?= Loc::getMessage('ALL_CATEGORIES') ?></option>
                                        <?php foreach ($select as $dir){?>
                                            <option value="<?=$dir['UF_NAME']?>" <?=$dir['UF_NAME']==$_REQUEST['dir']?'selected':''?>><?=$dir['UF_NAME']?></option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>
                            <?php if($USER->IsAdmin()){?>
                                <div class="form-group">
                                    <label for=""><?= Loc::getMessage('DEALER') ?></label>
                                    <div class="select">
                                        <select class="select2" name="dealer_id">
                                            <?php
                                            foreach (\Helpers\DealerHelper::getList(['ACTIVE' => 'Y'], ['ID', 'NAME']) as $item) {?>
                                                <option value="<?=$item['ID']?>"<?=$item['ID']==$dealer_id?' selected':'';?>><?=$item['NAME']?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                            <?php }?>
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
                                    <th rowspan="2"></th>
                                    <?php foreach ($dealer['ROLES_LIST'] as $role_){?>
                                        <th><b><?=$role_['NAME']?></b></th>
                                    <?php }?>
                                    <th>TOTAL</th>
                                </tr>
                                <tr>
                                    <?php foreach ($dealer['ROLES_LIST'] as $role__){
                                        $total_users+=count($role__['USERS'])?>
                                        <th class="text-center"><b><?=count($role__['USERS'])?></b></th>
                                    <?php }?>
                                    <th><?=$total_users?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course){
                                    $total=0;?>
                                    <tr style="height: 50px">
                                        <td class="text-left"><a href="/cabinet/diller/reports/course_completions/<?=$course['ID']?>/" target="_blank"><?=$course['NAME']?></a></td>
                                        <?php foreach ($dealer['ROLES_LIST'] as $role){
                                            $total+=(int)$role['COURSES'][$course['ID']]['COMPLETED']?>
                                            <td><?=$role['COURSES'][$course['ID']]?$role['COURSES'][$course['ID']]['COMPLETED']:'-'?></td>
                                        <?php }?>
                                        <td><?=$total?></td>
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
