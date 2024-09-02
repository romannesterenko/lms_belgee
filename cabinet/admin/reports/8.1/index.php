<?php

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER;
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
$dealers = \Models\Dealer::getAllWithoutIds([360, 292]);
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
$regional_marketing = [];
foreach ($dealers as $dealer){
    $not_show_ids = [2, 4714];
    if(in_array((int)$dealer['PROPERTY_REGIONAL_VALUE'], $not_show_ids))
        continue;
    if(in_array((int)$dealer['PROPERTY_REGIONAL_PPO_VALUE'], $not_show_ids))
        continue;
    if(in_array((int)$dealer['PROPERTY_REGIONAL_MARKETING_VALUE'], $not_show_ids))
        continue;
    if((int)$dealer['PROPERTY_REGIONAL_PPO_VALUE']>1)
        $regional_ppo[$dealer['PROPERTY_REGIONAL_PPO_VALUE']] = \Models\User::getFullName($dealer['PROPERTY_REGIONAL_PPO_VALUE']);
    if((int)$dealer['PROPERTY_REGIONAL_VALUE']>1)
        $regional_op[$dealer['PROPERTY_REGIONAL_VALUE']] = \Models\User::getFullName($dealer['PROPERTY_REGIONAL_VALUE']);
    if((int)$dealer['PROPERTY_REGIONAL_MARKETING_VALUE']>1)
        $regional_marketing[$dealer['PROPERTY_REGIONAL_MARKETING_VALUE']] = \Models\User::getFullName($dealer['PROPERTY_REGIONAL_MARKETING_VALUE']);
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
                <h2 class="h2 center">Отчет по посещаемости тренингов (Новый шаблон)</h2>
                <h4 class="h4 center">Предварительный фильтр</h4>
                <div class="form-div">
                    <form class="report_generator" action="report.php" method="post" style="">
                        <div class="form-group selectable">
                            <label for="">Подразделение</label>
                            <select class="js-example-basic-multiple" name="direction" style="width: 100%;">
                                <option value="all">Все</option>
                                <option value="S01">ОП</option>
                                <option value="A01">ППО</option>
                                <option value="M01">Маркетинг</option>
                            </select>
                        </div>
                        <?php if(\Helpers\UserHelper::isLocalAdminGMR()){?>
                            <input type="hidden" id="is_local_admin_gmr" value="Y">
                            <div class="form-group selectable">
                                <label for="">Страна</label>
                                <select class="js-example-basic-multiple" name="country" style="width: 100%;">
                                    <option value="">Выбрать страну</option>
                                    <?php foreach (\Models\Dealer::getCountriesList() as $country_id => $country){?>
                                        <option value="<?=$country_id?>"<?=$_REQUEST['country'] && $country_id == $_REQUEST['country']?' selected':''?>><?=$country?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="form-group selectable">
                                <label for="">Региональный менеджер ОП</label>
                                <select class="js-example-basic-multiple" name="regional_op[]" multiple="multiple" style="width: 100%;">
                                    <?php foreach ($regional_op as $id => $regional_name){?>
                                        <option value="<?=$id?>"<?=$_REQUEST['regional_op']&&in_array($id, $_REQUEST['regional_op'])?' selected':''?>><?=$regional_name?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="form-group selectable">
                                <label for="">Региональный менеджер ППО</label>
                                <select class="js-example-basic-multiple" name="regional_ppo[]" multiple="multiple" style="width: 100%;">
                                    <?php foreach ($regional_ppo as $id => $regional_name){?>
                                        <option value="<?=$id?>"<?=$_REQUEST['regional_ppo']&&in_array($id, $_REQUEST['regional_ppo'])?' selected':''?>><?=$regional_name?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="form-group selectable">
                                <label for="">Региональный менеджер Маркетинг</label>
                                <select class="js-example-basic-multiple" name="regional_marketing[]" multiple="multiple" style="width: 100%;">
                                    <?php foreach ($regional_marketing as $id => $regional_name){?>
                                        <option value="<?=$id?>"<?=$_REQUEST['regional_marketing']&&in_array($id, $_REQUEST['regional_marketing'])?' selected':''?>><?=$regional_name?></option>
                                    <?php }?>
                                </select>
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
                        <?php } else {?>
                            <input type="hidden" name="dealer_codes[]" value="<?=\Models\User::getDealerByUser()?>">
                        <?php }?>
                        <div class="form-group selectable">
                            <label for="">Роль</label>
                            <select class="js-example-basic-multiple" name="role[]" multiple="multiple" style="width: 100%;">
                                <?php foreach ($roles as $id => $role){?>
                                    <option value="<?=$id?>"<?=$_REQUEST['role']&&in_array($id, $_REQUEST['role'])?' selected':''?>><?=$role?></option>
                                <?php }?>
                            </select>
                        </div>
                        <?php if(\Helpers\UserHelper::isLocalAdminGMR()){?>
                            <div class="form-group">
                                <div class="form-group">
                                    <label for="">Статус сотрудника</label>
                                    <div class="form-group" style="display: flex">
                                        <div class="checkbox-item" style="padding-right: 20px">
                                            <input type="checkbox" id="registered_employee" name="registered_employee" checked style="display: none">
                                            <label for="registered_employee" style="padding-left: 30px;">Зарегистрирован</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox" id="deleted_employee" name="deleted_employee" <?=$_REQUEST['deleted_employee']=='on'?'checked':''?> style="display: none">
                                            <label for="deleted_employee" style="padding-left: 30px;">Удален</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php }?>
                        <div class="form-group selectable">
                            <label for="">ФИО</label>
                            <select class="js-example-basic-multiple" name="fio[]" multiple="multiple" style="width: 100%;">
                                <?php foreach ($employees as $employee){?>
                                    <option value="<?=$employee['ID']?>"<?=$_REQUEST['fio']&&in_array($employee['ID'], $_REQUEST['fio'])?' selected':''?>><?=$employee['LAST_NAME']?> <?=$employee['NAME']?> (<?=$employee['ID']?>)</option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="form-group">
                                <label for="">Статус курса</label>
                                <div class="form-group" style="display: flex">
                                    <div class="checkbox-item" style="padding-right: 20px">
                                        <input type="checkbox" id="status-1" name="status_complete" <?=$_REQUEST['status_complete']=='on'?'checked':''?> style="display: none">
                                        <label for="status-1" style="padding-left: 30px;">Пройден</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="status-2" name="status_not_complete" <?=$_REQUEST['status_not_complete']=='on'?'checked':''?> style="display: none">
                                        <label for="status-2" style="padding-left: 30px;">Не пройден</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group selectable">
                            <label for="">Курс</label>
                            <select class="js-example-basic-multiple" name="courses[]" multiple style="width: 100%;">
                                <?php foreach ($courses as $course){?>
                                    <option value="<?=$course['ID']?>"<?=$_REQUEST['courses']&&in_array($course['ID'], $_REQUEST['courses'])?' selected':''?>><?=$course['NAME']?></option>
                                <?php }?>
                            </select>
                        </div>
                        <?php $cities = \Helpers\HLBlockHelper::get(\Helpers\HLBlockHelper::initialize('city'), [], ['ID', 'UF_NAME', 'UF_XML_ID'], ['UF_NAME' => 'ASC'])?>
                        <div class="form-group selectable">
                            <label for="">Город</label>
                            <select class="js-example-basic-multiple" name="city" style="width: 100%;">
                                <option value="all">Не выбрано</option>
                                <?php foreach ($cities as $city){?>
                                    <option value="<?=$city['UF_XML_ID']?>"><?=$city['UF_NAME']?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="form-group">
                                <div class="form-group" style="display: flex">
                                    <div class="checkbox-item" style="padding-right: 20px">
                                        <input type="checkbox" id="need_and" name="need_and" style="display: none">
                                        <label for="need_and" style="padding-left: 30px;">Точное совпадение для курса</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <div class="form-group" style="width: 100%;margin-right: 20px;">
                                <label for="">Дата прохождения курса от</label>
                                <input type="date" name="course_date_before">
                            </div>
                            <div class="form-group" style="width: 100%;">
                                <label for="">Дата прохождения курса до</label>
                                <input type="date" name="course_date_after">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-group">
                                <div class="form-group" style="display: flex">
                                    <div class="checkbox-item" style="padding-right: 20px">
                                        <input type="checkbox" id="need_show_ids" name="need_show_ids" style="display: none" value="Y">
                                        <label for="need_show_ids" style="padding-left: 30px;">Выгружать идентификаторы</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" style="display: flex">
                            <div class="checkbox-item" style="padding-right: 20px">
                                <input type="checkbox" id="hide_fio" name="hide_fio" style="display: none" value="Y">
                                <label for="hide_fio" style="padding-left: 30px;">Сокращать ФИО до инициалов</label>
                            </div>
                        </div>
                        <div class="form-group" style="display: flex">
                            <div class="checkbox-item" style="padding-right: 20px">
                                <input type="checkbox" id="show_email" name="show_email" style="display: none" value="Y">
                                <label for="show_email" style="padding-left: 30px;">Показать Email</label>
                            </div>
                        </div>
                        <div class="form-group" style="display: flex; padding-top: 1rem;">
                            <div class="btn-center">
                                <button class="btn">Генерировать</button>
                            </div>
                        </div>
                    </form>
                    <div class="loader hidden">
                        <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" alt="">
                        <h4>Выполняется загрузка отчета</h4>
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
            $(document).on('submit', '.report_generator', function (){
                $('.loader').removeClass('hidden')
            });
            $(document).on('change', '[name="country"]', function (){
                processData()
            });
            $(document).on('change', '[name="op_servis_op"]', function (){
                processData()
            });
            $(document).on('change', '[name="deleted_employee"]', function (){
                processData()
            });
            $(document).on('change', '[name="registered_employee"]', function (){
                processData()
            });

            $(document).on('change', '[name="op_servis_servis"]', function (){
                processData()
            });

            $(document).on('change', '[name="direction"]', function (){
                processData()
            });

            $(document).on('change', '[name="fio[]"]', function (){
                processData()
            });

            $(document).on('change', '[name="regional_ppo[]"]', function (){
                processData()
            });

            $(document).on('change', '[name="regional_op[]"]', function (){
                processData()
            });

            $(document).on('change', '[name="regional_marketing[]"]', function (){
                processData()
            });

            $(document).on('change', '[name="dealer_codes[]"]', function (){
                $('[name="dealer_names[]"]').val($('[name="dealer_codes[]"]').val()).select2({language: "en"});
                //checkDealerForm('code');
                processData()
            });

            $(document).on('change', '[name="dealer_names[]"]', function (){
                $('[name="dealer_codes[]"]').val($('[name="dealer_names[]"]').val()).select2({language: "en"});
                //checkDealerForm('name');
                processData()
            });

            $(document).on('change', '[name="role[]"]', function (){
                //checkDealerForm('role');
                processData()
            });
        })
        function checkDealerForm(item){
            if(item==='ppo'){
                $('[name="dealer_names[]"]').empty();
                $('[name="dealer_codes[]"]').empty();
            }
            if(item==='code'){
                $('[name="dealer_names[]"]').val($('[name="dealer_codes[]"]').val()).select2({language: "en"});
                data = {
                    'dealer': $('[name="dealer_codes[]"]').val()
                }
            }
            if(item==='name'){
                $('[name="dealer_codes[]"]').val($('[name="dealer_names[]"]').val()).select2({language: "en"});
                data = {
                    'dealer': $('[name="dealer_names[]"]').val()
                }
            }
            let formData = new FormData($('.report_generator')[0]);
            formData.append('type_input', item)
            $.ajax({
                type: 'POST',
                url: '/local/templates/geely/ajax/report_fields.php',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function () {
                },
                success: function(response){
                    if(item==='ppo'){
                        if(response.dealers&&response.dealers.length>0){
                            for(var k in response.dealers) {
                                $('[name="dealer_names[]"]').append('<option value="'+response.dealers[k].ID+'">'+response.dealers[k].NAME+'</option>');
                                $('[name="dealer_codes[]"]').append('<option value="'+response.dealers[k].ID+'">'+response.dealers[k].CODE+'</option>');
                            }
                            $('[name="dealer_names[]"]').select2({language: "en"});
                            $('[name="dealer_codes[]"]').select2({language: "en"});
                        }
                        if(response.users&&response.users.length>0){
                            $('[name="fio[]"]').empty();
                            for(var k in response.users) {
                                $('[name="fio[]"]').append('<option value="'+response.users[k].ID+'">'+response.users[k].NAME+' '+response.users[k].LAST_NAME+'</option>');
                            }
                            $('[name="fio[]"]').select2({language: "en"});
                        }
                        if(response.roles&&response.roles.length>0){
                            $('[name="role[]"]').empty();
                            for(var k in response.roles) {
                                $('[name="role[]"]').append('<option value="'+response.roles[k].id+'">'+response.roles[k].name+'</option>');
                            }
                            $('[name="role[]"]').select2({language: "en"});
                        }
                    }
                    if(response.users&&response.users.length>0){
                        $('[name="fio[]"]').empty();
                        for(var k in response.users) {
                            $('[name="fio[]"]').append('<option value="'+response.users[k].ID+'">'+response.users[k].NAME+' '+response.users[k].LAST_NAME+'</option>');
                        }
                        $('[name="fio[]"]').select2({language: "en"});
                    }
                    if(response.roles&&response.roles.length>0){
                        $('[name="role[]"]').empty();
                        for(var k in response.roles) {
                            $('[name="role[]"]').append('<option value="'+response.roles[k].id+'">'+response.roles[k].name+'</option>');
                        }
                        $('[name="role[]"]').select2({language: "en"});
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                },
            });
        }
        function processData(){
            let formData = new FormData($('.report_generator')[0]);
            $.ajax({
                type: 'POST',
                url: '/local/templates/geely/ajax/report_fields1.php',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function () {
                    $('.loader').removeClass('hidden')
                },
                success: function(response){
                    console.log(response)
                    //Пользователи
                    let roles_f = new Array();
                    $('[name="fio[]"]').empty();
                    if(response.users_getlist&&response.users_getlist.length>0){
                        for(var k in response.users_getlist) {
                            let selected_string = '';
                            if(response.request.fio&&response.request.fio.length>0){
                                if(response.request.fio.includes(response.users_getlist[k].ID)) {
                                    selected_string = 'selected';
                                    if(response.users_getlist[k].UF_ROLE.length>0){
                                        for(let ur in response.users_getlist[k].UF_ROLE) {
                                            roles_f.push(response.users_getlist[k].UF_ROLE[ur])
                                        }
                                    }
                                }
                            }
                            $('[name="fio[]"]').append('<option '+selected_string+' value="'+response.users_getlist[k].ID+'">'+response.users_getlist[k].LAST_NAME+' '+response.users_getlist[k].NAME+' ('+response.users_getlist[k].ID+')</option>');
                        }
                        $('[name="fio[]"]').select2({language: "en"});
                    }

                    //роли
                    $('[name="role[]"]').empty();
                    if(response.roles&&response.roles.length>0){
                        for(var k in response.roles) {

                            let selected_string = '';
                            if(roles_f.length>0) {
                                roles_f.map(function (item) {
                                    if(parseInt(response.roles[k].ID)===parseInt(item)) {
                                        selected_string = 'selected';
                                    }
                                })
                            }
                            if(response.request.role&&response.request.role.length>0) {
                                if(response.request.role.includes(response.roles[k].ID)) {
                                    selected_string = 'selected';
                                }

                            }
                            $('[name="role[]"]').append('<option '+selected_string+' value="'+response.roles[k].ID+'">'+response.roles[k].NAME+'</option>');
                        }
                        $('[name="role[]"]').select2({language: "en"});
                    }

                    //курсы
                    $('[name="courses[]"]').empty();
                    if(response.courses&&response.courses.length>0){
                        for(var k in response.courses) {
                            let selected_string = '';
                            if(response.request.courses&&response.request.courses.length>0){
                                if(response.request.courses.includes(response.courses[k].ID))
                                    selected_string = 'selected';
                            }
                            $('[name="courses[]"]').append('<option '+selected_string+' value="'+response.courses[k].ID+'">'+response.courses[k].NAME+'</option>');
                        }
                        $('[name="courses[]"]').select2({language: "en"});
                    }

                    //дилеры
                    if($('#is_local_admin_gmr').val()=='Y') {
                        $('[name="dealer_codes[]"]').empty();
                        $('[name="dealer_names[]"]').empty();
                        if (response.dealers && response.dealers.length > 0) {
                            for (var k in response.dealers) {
                                let selected_string = '';
                                if (response.request.dealer_codes && response.request.dealer_codes.length > 0) {
                                    if (response.request.dealer_codes.includes(response.dealers[k].ID))
                                        selected_string = 'selected';
                                }
                                $('[name="dealer_codes[]"]').append('<option ' + selected_string + ' value="' + response.dealers[k].ID + '">' + response.dealers[k].CODE + '</option>');
                                $('[name="dealer_names[]"]').append('<option ' + selected_string + ' value="' + response.dealers[k].ID + '">' + response.dealers[k].NAME + '</option>');
                            }
                            $('[name="dealer_names[]"]').select2({language: "en"});
                            $('[name="dealer_codes[]"]').select2({language: "en"});
                        }
                    }

                    $('.loader').addClass('hidden')
                },
                error: function (xhr, ajaxOptions, thrownError) {
                },
            });
        }
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>