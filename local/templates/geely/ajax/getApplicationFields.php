<?php

use Bitrix\Main\Application;
use Helpers\StringHelpers;
use Models\Application as App;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

global $USER;
$response = [];
$request = Application::getInstance()->getContext()->getRequest()->getValues();
CModule::IncludeModule('iblock');
$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM","PROPERTY_*");
$arFilter = Array("ID" => $request['application'], "IBLOCK_ID"=>35, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
while($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();
    $arProps = $ob->GetProperties();
    $arFields['PROPERTIES'] = $arProps;
}
foreach ($arFields['PROPERTIES']['FIELDS']['VALUE'] as $one_field) {
    $text_array = StringHelpers::unserialize($one_field);
    if($text_array['HIDDEN'] == 'on'){
        $response['fields'][] = ['name' => 'UF_'.$text_array['FIELD_CODE'], 'value' => ''];
    } else {
        $value['VALUE'] = '';
        if ($text_array['AUTOMATIC'] == 'on') {
            $value = App::getLoadData($text_array, $request['user']);
        }
        $response['fields'][] = ['name' => 'UF_'.$text_array['FIELD_CODE'], 'value' => $value['VALUE']];
        unset($value);
    }
}
$response['request'] = $request;
echo json_encode($response);

