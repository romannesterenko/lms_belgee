<?php

use Bitrix\Main\Localization\Loc;
use Settings\Reports;

const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

global $USER, $APPLICATION;

$settings = Reports::getCompletionsReport();
$completion_obj = new \Teaching\CourseCompletion();
$dealer_id = (int)$_REQUEST['dealer_id'];
$_REQUEST['report_id'] = $dealer_id==0?1:$dealer_id;
if ((int)$_REQUEST['dealer_id']>0||(int)$_REQUEST['role_id']>0){
    $users = [];
    $dealer_users = [];
    $role_users = [];
    if((int)$_REQUEST['dealer_id']>0)
        $dealer_users = \Models\Employee::getByDealer((int)$_REQUEST['dealer_id']);
    if((int)$_REQUEST['role_id']>0)
        $role_users = \Models\User::getEmployeesByRoles([(int)$_REQUEST['role_id']]);
    $user_ids = [];
    if(count($dealer_users)>0&&count($role_users)>0) {
        foreach ($dealer_users as $dealer_user) {
            foreach ($role_users as $role_user) {
                if ($dealer_user['ID'] == $role_user['ID']) {
                    $user_ids[] = $dealer_user['ID'];
                }
            }
        }
    }else{
        if(count($dealer_users)>0) {
            foreach ($dealer_users as $dealer_user) {
                $user_ids[] = $dealer_user['ID'];
            }
        }
        if(count($role_users)>0){
            foreach ($role_users as $role_user) {
               $user_ids[] = $role_user['ID'];
            }
        }
    }
    if(count($user_ids)>0)
        $completions = $completion_obj->getCompletedItems($user_ids)->getArray();

}else{
    $completions = $completion_obj->getAllCompletedItems();
}
foreach ($completions as &$completion){
    $completion['COURSE'] = \Teaching\Courses::getById($completion['UF_COURSE_ID']);
    $completion['USER'] = \Models\User::find($completion['UF_USER_ID'], ['NAME', 'LAST_NAME', 'UF_DEALER', 'UF_ROLE']);
    $completion['DEALER'] = \Helpers\DealerHelper::getByUser($completion['UF_USER_ID'], ['NAME', 'CODE', 'PROPERTY_CITY']);
    $completion['DEALER']['REGIONAL_OP'] = \Models\Dealer::getRegionalOP($completion['DEALER']['ID']);
    $completion['DEALER']['REGIONAL_PPO'] = \Models\Dealer::getRegionalPPO($completion['DEALER']['ID']);
    $completion['DEALER']['ADMIN'] = \Models\User::getDCAdmin($completion['DEALER']['ID']);
}
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
                    <h2 class="h2 center"><?= Loc::getMessage('TITLE') ?></h2>
                    <?php if($USER->IsAdmin()){?>
                    <form style="display: flex; justify-content: space-between">
                        <div class="form-group" style="display: flex;">
                            <div class="form-group mr-20">
                                <label for=""><?= Loc::getMessage('DEALER') ?></label>
                                <div class="select">
                                    <select class="select2" name="dealer_id">
                                        <option value="0"><?= Loc::getMessage('ALL_DEALERS') ?></option>
                                        <?php foreach (\Helpers\DealerHelper::getList(['ACTIVE' => 'Y'], ['ID', 'NAME']) as $item) {?>
                                            <option value="<?=$item['ID']?>"<?=$item['ID']==$dealer_id?' selected':''?>><?=$item['NAME']?></option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for=""><?= Loc::getMessage('ROLE') ?></label>
                                <div class="select">
                                    <select class="select2" name="role_id">
                                        <option value="0"><?= Loc::getMessage('aLL_ROLES') ?></option>
                                        <?php foreach (\Teaching\Roles::getRolesList(['ACTIVE' => 'Y'], ['ID', 'NAME']) as $item) {?>
                                            <option value="<?=$item['ID']?>"<?=$item['ID']==(int)$_REQUEST['role_id']?' selected':''?>><?=$item['NAME']?></option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" style="display: flex; padding-top: 1rem;">
                            <div class="btn-center">
                                <button class="btn"><?= Loc::getMessage('SUVMIT') ?></button>
                            </div>
                            <div class="btn-center">
                                <a role="button" href="<?=$APPLICATION->GetCurPage()?>" class="btn"><?= Loc::getMessage('RESEET') ?></a>
                            </div>
                        </div>
                    </form>
                    <?php }?>
                    <div class="table-block">
                        <table class="table table-bordered table-striped table--white" id="table-report" style="padding-top: 25px">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Country</th>
                                    <th class="text-center">CITY</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">DLR code</th>
                                    <th class="text-center">DLR city</th>
                                    <th class="text-center">Whole Name</th>
                                    <th class="text-center">Sales Director</th>
                                    <th class="text-center">Aftersales Director</th>
                                    <th class="text-center">Exam date</th>
                                    <th class="text-center">Exam result</th>
                                    <th class="text-center">Parts  Manager</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($completions as $key => $completion_){?>
                                    <tr>
                                        <td class="text-center"><?=$key+1?></td>
                                        <td class="text-center">Russia</td>
                                        <td class="text-center"><?=$completion_['COURSE']['PROPERTIES']['CITY']?></td>
                                        <td class="text-center">3S(Sale+Spare part+Service)</td>
                                        <td class="text-center"><?=$completion_['DEALER']['CODE']?></td>
                                        <td class="text-center"><?=$completion_['DEALER']['PROPERTY_CITY_VALUE']?></td>
                                        <td class="text-center"><?=$completion_['DEALER']['NAME']?></td>
                                        <td class="text-center"><?=$completion_['DEALER']['REGIONAL_OP']?></td>
                                        <td class="text-center"><?=$completion_['DEALER']['REGIONAL_PPO']?></td>
                                        <td class="text-center"><?=$completion_['UF_COMPLETED_TIME']?></td>
                                        <td class="text-center"><?= Loc::getMessage('COMPLETED') ?> (<?= Loc::getMessage('SCORE') ?> <?=$completion_['UF_POINTS']?>)</td>
                                        <td class="text-center"><?=$completion_['USER']['NAME']?> <?=$completion_['USER']['LAST_NAME']?></td>
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
