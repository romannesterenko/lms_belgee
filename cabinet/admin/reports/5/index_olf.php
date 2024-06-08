<?php

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER;
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
$dealers = \Models\Dealer::getAll();
$role_ids = [];
$roles = \Models\Role::getArray(['ACTIVE' => "Y"]);
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
$select = \Teaching\Courses::getDirectionsList();
$select[] = [
    'UF_NAME' => 'OTHERS',
    'UF_XML_ID' => false,
];
?>

    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center" style="margin-bottom:10px">Отчет по продуктивности</h2>
                <h4 class="h4 center">Предварительный фильтр</h4>
                <div class="form-div">
                <form class="report_generator" action="report.php" method="post" style="">
                    <div class="form-group">
                        <div class="form-group">
                            <label for="">ОП / Сервис</label>
                            <div class="form-group" style="display: flex">
                                <div class="checkbox-item" style="padding-right: 20px">
                                    <input type="checkbox" id="op_servis_op" name="op_servis_op" <?=$_REQUEST['op_servis_op']=='on'?'checked':''?> style="display: none">
                                    <label for="op_servis_op" style="padding-left: 30px;">ОП</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="op_servis_servis" name="op_servis_servis" <?=$_REQUEST['op_servis_servis']=='on'?'checked':''?> style="display: none">
                                    <label for="op_servis_servis" style="padding-left: 30px;">Сервис</label>
                                </div>
                            </div>
                        </div>
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
                    <div class="form-group selectable">
                        <label for="">Роль</label>
                        <select class="js-example-basic-multiple" name="role[]" multiple="multiple" style="width: 100%;">
                            <?php foreach ( $roles as $id => $role ) {?>
                                <option value="<?=$id?>"<?=$_REQUEST['role']&&in_array($id, $_REQUEST['role'])?' selected':''?>><?=$role?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="form-group selectable">
                        <label for="">Категория курса</label>
                        <select class="js-example-basic-multiple" name="dir" style="width: 100%;">
                            <option value="0" style="padding-left: 5px">Все категории</option>
                            <?php foreach ($select as $dir){?>
                                <option value="<?=$dir['UF_NAME']?>" <?=$dir['UF_NAME']==$_REQUEST['dir']?'selected':''?>><?=$dir['UF_NAME']?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <div class="form-group" style="width: 100%;margin-right: 20px;">
                            <label for="">Дата начала (и позже):</label>
                            <input type="date" style="border-radius: 4px;" name="start_date">
                        </div>
                        <div class="form-group" style="width: 100%;">
                            <label for="">Дата окончания (и ранее):</label>
                            <input type="date" style="border-radius: 4px;" name="end_date">
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

            $(document).on('change', '[name="op_servis_servis"]', function (){
                processData()
            });

            $(document).on('change', '[name="regional_ppo[]"]', function (){
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
                        if(response.roles&&response.roles.length>0){
                            $('[name="role[]"]').empty();
                            for(var k in response.roles) {
                                $('[name="role[]"]').append('<option value="'+response.roles[k].id+'">'+response.roles[k].name+'</option>');
                            }
                            $('[name="role[]"]').select2({language: "en"});
                        }
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
                    $('[name="fio[]"]').empty();
                    if(response.users_getlist&&response.users_getlist.length>0){
                        for(var k in response.users_getlist) {
                            let selected_string = '';
                            if(response.request.fio&&response.request.fio.length>0){
                                if(response.request.fio.includes(response.users_getlist[k].ID))
                                    selected_string = 'selected';
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
                            if(response.request.role&&response.request.role.length>0){
                                if(response.request.role.includes(response.roles[k].ID))
                                    selected_string = 'selected';
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