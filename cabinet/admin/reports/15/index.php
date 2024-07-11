<?php

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER;
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
$dealers = \Models\Dealer::getAll(['ID', 'NAME', 'CODE', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_MARKETING', 'PROPERTY_CITY']);
$role_ids = [];
$roles = \Models\Role::getArray(['>ID' => 0]);
$dealers_names = [];
$dealer_codes = [];
$dealer_codes = [];
$regional_ppo = [];
$regional_op = [];
$regional_marketing = [];
$cities = [];
foreach ($dealers as $dealer){
    $not_show_ids = [2, 4714];
    if(in_array((int)$dealer['PROPERTY_REGIONAL_VALUE'], $not_show_ids))
        continue;
    if(in_array((int)$dealer['PROPERTY_REGIONAL_PPO_VALUE'], $not_show_ids))
        continue;
    if($dealer['PROPERTY_CITY_VALUE']&&!in_array($dealer['PROPERTY_CITY_VALUE'], $cities))
        $cities[] = $dealer['PROPERTY_CITY_VALUE'];
    if((int)$dealer['PROPERTY_REGIONAL_PPO_VALUE']>1)
        $regional_ppo[$dealer['PROPERTY_REGIONAL_PPO_VALUE']] = \Models\User::getFullName($dealer['PROPERTY_REGIONAL_PPO_VALUE']);
    if((int)$dealer['PROPERTY_REGIONAL_VALUE']>1)
        $regional_op[$dealer['PROPERTY_REGIONAL_VALUE']] = \Models\User::getFullName($dealer['PROPERTY_REGIONAL_VALUE']);
    if((int)$dealer['PROPERTY_REGIONAL_MARKETING_VALUE'] > 1)
        $regional_marketing[$dealer['PROPERTY_REGIONAL_MARKETING_VALUE']] = \Models\User::getFullName($dealer['PROPERTY_REGIONAL_MARKETING_VALUE']);
    $dealers_names[$dealer['ID']] = $dealer['NAME'];
    if(!empty($dealer['CODE']))
        $dealer_codes[$dealer['ID']] = $dealer['CODE'];
}
$months = [
    "01" => "Январь",
    "02" => "Февраль",
    "03" => "Март",
    "04" => "Апрель",
    "05" => "Май",
    "06" => "Июнь",
    "07" => "Июль",
    "08" => "Август",
    "09" => "Сентябрь",
    "10" => "Октябрь",
    "11" => "Ноябрь",
    "12" => "Декабрь",
];
$years = range((int)date('Y')-5, (int)date('Y'));
?>

    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center" style="margin-bottom:10px">Рейтинг дилеров по % аттестованного персонала</h2>
                <h4 class="h4 center">Предварительный фильтр</h4>
                <div class="form-div">
                    <form class="report_generator" action="report.php" method="post" style="">
                        <div class="form-group selectable">
                            <label for="">Подразделение</label>
                            <select class="js-example-basic-multiple" name="direction" style="width: 100%;">
                                <option value="all">Все подразделения</option>
                                <option value="S01">ОП</option>
                                <option value="A01">ППО</option>
                                <option value="M01">Маркетинг</option>
                            </select>
                        </div>
                        <div class="form-group selectable">
                            <label for="">Город</label>
                            <select class="js-example-basic-multiple" name="city[]" multiple="multiple" style="width: 100%;">
                                <?php foreach ($cities as $city){?>
                                    <option value="<?=$city?>"<?=$_REQUEST['city']&&in_array($city, $_REQUEST['city'])?' selected':''?>><?=$city?></option>
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
                                <?php foreach ($roles as $id => $role){?>
                                    <option value="<?=$id?>"<?=$_REQUEST['role']&&in_array($id, $_REQUEST['role'])?' selected':''?>><?=$role?></option>
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

            $(document).on('change', '[name="direction"]', function (){
                processData($(this))
            });

            $(document).on('change', '[name="regional_ppo[]"]', function (){
                processData($(this))
            });

            $(document).on('change', '[name="regional_op[]"]', function (){
                processData($(this))
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
            $(document).on('change', '[name="dealer_codes[]"]', function (){
                $('[name="dealer_names[]"]').val($('[name="dealer_codes[]"]').val()).select2({language: "en"});
            });

            $(document).on('change', '[name="dealer_names[]"]', function (){
                $('[name="dealer_codes[]"]').val($('[name="dealer_names[]"]').val()).select2({language: "en"});
            });
        })
        function processData(main_entity){
            let formData = new FormData($('.report_generator')[0]);
            $.ajax({
                type: 'POST',
                url: '/local/templates/geely/ajax/report_fields_ratings.php',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function () {
                    $('.loader').removeClass('hidden')
                },
                success: function(response){
                    console.log(response)
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
                    $('[name="regional_op[]"]').empty();
                    if(response.regional_op && response.regional_op.length > 0){
                        for (var k in response.regional_op) {
                            let selected_string = '';
                            if (response.request.regional_op&&response.request.regional_op.includes(response.regional_op[k].ID)) {

                                selected_string = 'selected';

                            }
                            $('[name="regional_op[]"]').append('<option ' + selected_string + ' value="' + response.regional_op[k].ID + '">' + response.regional_op[k].NAME + '</option>');
                        }
                        $('[name="regional_op[]"]').select2({language: "en"});
                    }
                    $('[name="regional_ppo[]"]').empty();
                    if(response.regional_ppo && response.regional_ppo.length > 0){
                        for (var k in response.regional_ppo) {
                            let selected_string = '';
                            if (response.request.regional_ppo&&response.request.regional_ppo.includes(response.regional_ppo[k].ID))
                                selected_string = 'selected';
                            $('[name="regional_ppo[]"]').append('<option ' + selected_string + ' value="' + response.regional_ppo[k].ID + '">' + response.regional_ppo[k].NAME + '</option>');
                        }
                        $('[name="regional_ppo[]"]').select2({language: "en"});
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
                    $('.loader').addClass('hidden')
                },
                error: function (xhr, ajaxOptions, thrownError) {
                },
            });
        }
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>