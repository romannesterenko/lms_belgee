<?php

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER;
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
$dealers = \Models\Dealer::getAll();

$employees = \Models\Employee::getListByDealer(\Models\User::getDealerByUser(), ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE']);

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
        <h2 class="h2 center" style="margin-bottom:10px">Отчет по посещаемости тренингов</h2>
        <h4 class="h4 center">Предварительный фильтр</h4>
        <div class="form-div">
            <form class="report_generator" action="report.php" method="post" style="">
                <div class="form-group">
<!--                    <div class="form-group">-->
<!--                        <label for="">ОП / Сервис</label>-->
<!--                        <div class="form-group" style="display: flex">-->
<!--                            <div class="checkbox-item" style="padding-right: 20px">-->
<!--                                <input type="checkbox" id="op_servis_op" name="" --><?php //=$_REQUEST['op_servis_op']=='on'?'checked':''?><!-- style="display: none">-->
<!--                                <label for="op_servis_op" style="padding-left: 30px;">ОП</label>-->
<!--                            </div>-->
<!--                            <div class="checkbox-item">-->
<!--                                <input type="checkbox" id="op_servis_servis" name="op_servis_servis" --><?php //=$_REQUEST['op_servis_servis']=='on'?'checked':''?><!-- style="display: none">-->
<!--                                <label for="op_servis_servis" style="padding-left: 30px;">Сервис</label>-->
<!--                            </div>-->
<!--                        </div>-->
                    <!--                    </div>-->
                        <div class="form-group selectable">
                            <label for="">Подразделение</label>
                            <select class="js-example-basic-multiple" name="direction" style="width: 100%;">
                                <option value="all">Все</option>
                                <option value="S01">ОП</option>
                                <option value="A01">ППО</option>
                                <option value="M01">Маркетинг</option>
                            </select>
                        </div>

                </div>
                <input type="hidden" name="dealer_codes[]" value="<?=\Models\User::getDealerByUser()?>">
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
                
                <div class="form-group selectable">
                    <label for="">Курс</label>
                    <select class="js-example-basic-multiple" name="courses[]" multiple style="width: 100%;">
                        <?php foreach ($courses as $course){?>
                            <option value="<?=$course['ID']?>"<?=$_REQUEST['courses']&&in_array($course['ID'], $_REQUEST['courses'])?' selected':''?>><?=$course['NAME']?></option>
                        <?php }?>
                    </select>
                </div>

<!--                <div class="form-group">-->
<!--                    <div class="form-group">-->
<!--                        <div class="form-group" style="display: flex">-->
<!--                            <div class="checkbox-item" style="padding-right: 20px">-->
<!--                                <input type="checkbox" id="need_and" name="need_and" style="display: none">-->
<!--                                <label for="need_and" style="padding-left: 30px;">Точное совпадение для курса</label>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
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

                    $('.loader').addClass('hidden')
                },
                error: function (xhr, ajaxOptions, thrownError) {
                },
            });
        }
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>