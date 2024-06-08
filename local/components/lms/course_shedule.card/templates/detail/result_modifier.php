<?php
/** @var array $arResult */

if(!empty($arResult['ITEM']['SCHEDULE']['PROPERTIES']['TRAINERS'])&&count($arResult['ITEM']['SCHEDULE']['PROPERTIES']['TRAINERS'])>0){
    $ids = [];
    foreach ($arResult['ITEM']['SCHEDULE']['PROPERTIES']['TRAINERS'] as $item)
        $ids[] = $item['VALUE'];
    $arResult['SPEAKERS'] = \Teaching\Speakers::getList(['ID' => $ids], ['ID', 'NAME', 'PREVIEW_TEXT', 'PREVIEW_PICTURE'], ['ID' => $ids]);
}
$arResult['SPEAKERS'] = (is_array($arResult['SPEAKERS'])&&count($arResult['SPEAKERS'])>0)?array_values($arResult['SPEAKERS']):[];
$arResult['MAIN_SPEAKER'] = $arResult['SPEAKERS'][0];
if (!empty($arResult['ITEM']['PROPERTIES']['COURSE_MARERIALS_FILES'])&&count($arResult['ITEM']['PROPERTIES']['COURSE_MARERIALS_FILES'])){
    $ids = [];
    foreach ($arResult['ITEM']['PROPERTIES']['COURSE_MARERIALS_FILES'] as $COURSE_MARERIALS_FILE){
        $ids[] = $COURSE_MARERIALS_FILE['VALUE'];
    }
    $arResult['MATERIALS_FILES'] = \Teaching\MaterialsFiles::getList(['ID' => $ids], ['ID', 'NAME', 'PROPERTY_FILE']);
}
$arResult['USER']['HAS_RIGHTS_TO_SET_COURSE'] = \Models\User::hasRightsToSet();
$arResult['USER']['HAS_RIGHTS_TO_ENROLL_EMPLOYEE'] = \Models\User::hasRightsToEnrollEmployee();
$arResult['ITEM']['FOR_SETTING'] = true;