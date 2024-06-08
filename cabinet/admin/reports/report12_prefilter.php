<?php

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER;
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
$dealers = \Models\Dealer::getAll();
if($USER->isAdmin()){
    $employees = \Models\Employee::getListByDealer(0, ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE']);
} else {
    $employees = \Models\Employee::getListByDealer(\Models\User::getDealerByUser(), ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE']);

}
$role_ids = [];
foreach ($employees as $employee){
    if(check_full_array($employee['UF_ROLE']))
        $role_ids = array_merge($role_ids, $employee['UF_ROLE']);
}
$roles = \Models\Role::getArray(['ID' => $role_ids]);
$courses = \Models\Course::getList(['ACTIVE' => 'Y'], ['ID', 'NAME']);
$dealers_names = [];
$dealer_codes = [];
$dealer_codes = [];
$regional_ppo = [];
$regional_op = [];
foreach ($dealers as $dealer){
    $not_show_ids = [2, 4714];
    if(in_array((int)$dealer['PROPERTY_REGIONAL_VALUE'], $not_show_ids))
        continue;
    if(in_array((int)$dealer['PROPERTY_REGIONAL_PPO_VALUE'], $not_show_ids))
        continue;
    if(empty($dealer['CODE']))
        continue;
    if((int)$dealer['PROPERTY_REGIONAL_PPO_VALUE']>1)
        $regional_ppo[$dealer['PROPERTY_REGIONAL_PPO_VALUE']] = \Models\User::getFullName($dealer['PROPERTY_REGIONAL_PPO_VALUE']);
    if((int)$dealer['PROPERTY_REGIONAL_VALUE']>1)
        $regional_op[$dealer['PROPERTY_REGIONAL_VALUE']] = \Models\User::getFullName($dealer['PROPERTY_REGIONAL_VALUE']);
    $dealers_names[$dealer['ID']] = $dealer['NAME'];
    if(!empty($dealer['CODE']))
        $dealer_codes[$dealer['ID']] = $dealer['CODE'];
}
?>

    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Штатное расписание</h2>
                <div class="form-div">
                    <form class="report_generator" action="report12.php" method="post" style="">
                        <div class="form-group">
                            <div class="form-group">
                                <label for="">ОП / Сервис</label>
                                <div class="form-group" style="display: flex">
                                    <div class="checkbox-item" style="padding-right: 20px">
                                        <input type="checkbox" id="op_servis_op" name="op_servis_op" checked style="display: none">
                                        <label for="op_servis_op" style="padding-left: 30px;">ОП</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="op_servis_servis" name="op_servis_servis" style="display: none">
                                        <label for="op_servis_servis" style="padding-left: 30px;">Сервис</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group selectable">
                            <label for="">Код дилера</label>
                            <select class="js-example-basic-multiple" name="dealer_codes[]" multiple="multiple" style="width: 100%;">
                                <?php foreach ($dealer_codes as $id => $code){?>
                                    <option value="<?=$id?>"<?=$_REQUEST['dealer_codes']&&in_array($id, $_REQUEST['dealer_codes'])?' selected':''?>><?=$code?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="form-group selectable">
                            <label for="">Название дилера</label>
                            <select class="js-example-basic-multiple" name="dealer_names[]" multiple="multiple" style="width: 100%;">
                                <?php foreach ($dealers_names as $id => $name){?>
                                    <option value="<?=$id?>"<?=$_REQUEST['dealer_codes']&&in_array($id, $_REQUEST['dealer_names'])?' selected':''?>><?=$name?></option>
                                <?php }?>
                            </select>
                        </div>

                        <div class="form-group" style="display: flex; padding-top: 1rem;">
                            <div class="btn-center">
                                <button class="btn">Генерировать</button>
                            </div>
                        </div>
                    </form>
                    <div class="loader hidden">
                        <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                        <h4>Обновление полей формы</h4>
                    </div>
                </div>
                <div class="table-block"></div>
            </div>
        </div>
    </div>
    <style>
        .form-group.selectable input{
            height: 20px!important;
        }
        .select2-selection__choice{
            padding: 10px!important;
        }
        .select2-selection__choice__remove{
            padding-right: 5px!important;
        }
        .form-div{
            position: relative;
        }
        .loader.hidden{
            display: none!important;
        }
        .loader{
            position: absolute;
            background-color: white;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0.7;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .loader img{
            opacity: 1;
            width: 200px;
            height: 200px;
        }
    </style>
    <script>
        $(function (){

            $(document).on('change', '.checkbox-item input[type="checkbox"]', function (){
                if($(this).attr('name')=='op_servis_op'){
                    //if(!$('[name="op_servis_servis"]').is(':checked')){
                        $('label[for="op_servis_servis"]').trigger('click');
                    //}
                }
                if($(this).attr('name')=='op_servis_servis'){
                    //if(!$('[name="op_servis_op"]').is(':checked')){
                        $('label[for="op_servis_op"]').trigger('click');
                    //}
                }
            });
            $(document).on('change', '[name="dealer_codes[]"]', function (){
                $('[name="dealer_names[]"]').val($(this).val()).select2({language: "en"});
            });

            $(document).on('change', '[name="dealer_names[]"]', function (){
                $('[name="dealer_codes[]"]').val($(this).val()).select2({language: "en"});
            });
        })
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>