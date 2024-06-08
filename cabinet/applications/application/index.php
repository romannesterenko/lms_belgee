<?php
use Helpers\PageHelper;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
use Helpers\StringHelpers;
use Models\Application;
use Models\Dealer;
use Models\Role;
use Models\User;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION, $USER;

$app_id = explode('?', $_REQUEST['app_id'])[0];
function GetEntityDataClass($HlBlockId) {
    if (empty($HlBlockId) || $HlBlockId < 1)
    {
        return false;
    }
    $hlblock = HLBT::getById($HlBlockId)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
    return $entity_data_class;
}
if($app_id > 0) {
    $user = User::getCurrent(['ID', 'EMAIL']);
$exists_apps = Application::getAddedItemsByDealer($app_id, Dealer::getByEmployee());

if($_REQUEST['sended'] = 'Y' && $_REQUEST['application_id'] > 0){

    $filter = array(
        'NAME' => 'DealerApplication'.$_REQUEST['application_id'],
        'TABLE_NAME' => 'dealer_application'.$_REQUEST['application_id']
    );
    $hlblock = HLBT::getList(['filter' => $filter])->fetch();
    if(!check_full_array($hlblock)){

    } else {
        $entity_data_class = GetEntityDataClass($hlblock['ID']);
        $fields = [
            'UF_USER_ID' => $USER->GetID(),
            'UF_DEALER_ID' => Dealer::getByEmployee(),
            'UF_CREATED_AT' => date('d.m.Y H:i:s'),
            'UF_APPROVED' => false,
        ];
        foreach ($_REQUEST as $request_name => $request_value) {
            if(str_contains($request_name, 'UF_')){
                $fields[$request_name] = $request_value;
            }
        }
        $result = $entity_data_class::add($fields);
        if ($result->isSuccess()) {
            LocalRedirect($APPLICATION->GetCurPage()."?".http_build_query(['success'=>'Y']));
        }
    }
}
$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM","PROPERTY_*");
$arFilter = Array("ID" => $app_id, "IBLOCK_ID"=>35, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
while($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();
    $arProps = $ob->GetProperties();
    $arFields['PROPERTIES'] = $arProps;
}
$apps_count = Dealer::getAppsCount($app_id, Dealer::getByEmployee());
$APPLICATION->SetTitle('Заявка '.$arFields['NAME']);
$my_apps = Application::getAddedItems($app_id, Dealer::getByEmployee(), $user['ID']);
$roles = Role::getArray(['ID' => $arFields['PROPERTIES']['ROLES']['VALUE']]);
$users = User::get(['UF_ROLE' => $arFields['PROPERTIES']['ROLES']['VALUE'], 'UF_DEALER' => Dealer::getByEmployee()]);
foreach ($users as $key_user => &$temp_user) {
    if($arFields['PROPERTIES']['DECLINE_MULTIPLE']['VALUE_ENUM_ID'] == 163) {
        foreach ($exists_apps as $exists_app) {
            if ($exists_app['UF_EMAIL'] == $temp_user['EMAIL']) {
                unset($users[$key_user]);
                continue 2;
            }
        }
    }
    foreach ($roles as $role_id => $role_name){
        if(in_array($role_id, $temp_user['UF_ROLE'])){
            $temp_user['ROLE_ARRAY'][] = $role_name;
        }
    }
}
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <h2 class="h2 center lowercase">
                <?=$arFields['NAME']?>
            </h2>
            <?php if($_REQUEST['success'] == 'Y'){?>
                <p style="color: green; text-align: center; margin-bottom: 20px">Заявка успешно добавлена</p>
            <?php }?>
            <?php if(check_full_array($users)) {?>
                <div class="form-group selectable">
                    <label for="">От кого оформляется заявка</label>
                    <select class="js-example-basic-multiple" id="user_for_load" name="user_for_load" style="width: 100%;">
                        <?php foreach($users as $user){?>
                            <option value="<?=$user['ID']?>"<?=$user['ID']==$USER->GetID()?' selected':''?>><?=$user['NAME']?> <?=$user['LAST_NAME']?> (<?=implode(', ', $user['ROLE_ARRAY'])?>)</option>
                        <?php }?>
                    </select>
                </div>
            <?php }?>
        <?php
        if($apps_count!=0 && count($exists_apps) >= $apps_count) {?>
                <p style="color: green; text-align: center">Количество заявок для вашего дилера достигло лимита</p>
            </div>
        <?php } elseif (!check_full_array($users)) {?>
                <p style="color: green; text-align: center">Нет доступных сотрудников для заполнения</p>
            </div>
        <?php } else {?>
        <form action="" class="content" method="post" style="margin: 0 auto">
                <input type="hidden" name="application_id" value="<?=$arFields['ID']?>">
                <input type="hidden" name="sended" value="Y">
                <?php foreach ($arFields['PROPERTIES']['FIELDS']['VALUE'] as $one_field) {
                    $text_array = StringHelpers::unserialize($one_field);
                    if($text_array['HIDDEN'] == 'on'){?>
                        <input type="hidden" name="UF_<?=$text_array['FIELD_CODE']?>" value="">
                    <?php } else {
                        $value['VALUE'] = '';
                        if($text_array['AUTOMATIC']=='on'){
                            $value = Application::getLoadData($text_array);
                        }?>
                        <div class="form-group">
                            <label for=""><?=$text_array['NAME']?><?=$text_array['REQUIRED'] == 'on'?"<span style='color:red'>*</span>":''?>:</label>
                            <input type="<?=$text_array['STRING']?>" name="UF_<?=$text_array['FIELD_CODE']?>" <?=$text_array['AUTOMATIC']=='on'?'disabled':''?> value="<?=$value['VALUE']?>" <?=$text_array['REQUIRED'] == 'on'?"required":""?>>
                        </div>
                        <?php if(!empty($value['HIDDEN'])) {?>
                            <input type="hidden" name="UF_<?=$text_array['FIELD_CODE']?>" value="<?=$value['HIDDEN']?>">
                        <?php }?>
                    <?php }
                    unset($value)?>
                <?php }?>

                <div class="btn-center">
                    <button class="btn">Отправить</button>
                </div>
            </form>
        <?php }?>
    </div>
<?php }?>
    <script>
        $(function (){
            loadFormFields($('#user_for_load').val())
            $(document).on('change', '#user_for_load', function (){
                loadFormFields($('#user_for_load').val())
            });
        })

        function loadFormFields(user_id) {
            $.ajax({
                type: 'POST',
                url: '/local/templates/geely/ajax/getApplicationFields.php',
                data: {
                    user: user_id,
                    application: '<?=$app_id?>'
                },
                dataType: 'json',
                beforeSend: function () {
                },
                success: function(response){
                    for (let i in response.fields) {
                        $(`[name=${response.fields[i].name}]`).val(response.fields[i].value)
                    }

                },
                error: function (xhr, ajaxOptions, thrownError) {
                },
            });
        }
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>