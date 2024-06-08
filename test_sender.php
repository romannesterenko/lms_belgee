<?php
use Helpers\PageHelper;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
use Helpers\StringHelpers;
use Models\Application;
use Models\Dealer;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION, $USER;
$APPLICATION->SetTitle('Баланс');
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
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <form class="content">
            <?php $arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM","PROPERTY_*");
            $arFilter = Array("ID" => 123965, "IBLOCK_ID"=>35, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
            $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
            while($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $arProps = $ob->GetProperties();
                $arFields['PROPERTIES'] = $arProps;
            }?>
            <h2 class="h2 center lowercase">
                <?=$arFields['NAME']?>
            </h2>
            <?php if($_REQUEST['success'] == 'Y'){?>
                <p style="color: green; text-align: center; margin-bottom: 20px">Заявка успешно добавлена</p>
            <?php }?>
            <form action="" method="post" style="max-width: 500px; margin: 0 auto">
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
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>