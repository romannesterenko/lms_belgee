<? /** @var array $arResult */
if(count($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_TRAINERS_VALUE'])>0){
    $arResult['SPEAKERS'] = \Teaching\Speakers::getList(['ID' => $arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_TRAINERS_VALUE']], ['ID', 'NAME', 'PREVIEW_TEXT', 'PREVIEW_PICTURE']);
    $arResult['MAIN_SPEAKER'] = $arResult['SPEAKERS'][$arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_TRAINERS_VALUE'][0]];
}
if (count($arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_COURSE_MARERIALS_FILES_VALUE'])>0){
    $arResult['MATERIALS_FILES'] = \Teaching\MaterialsFiles::getList(['ID' => $arResult['ITEM']['PROPERTY_COURSE_ITEM']['PROPERTY_COURSE_MARERIALS_FILES_VALUE']], ['ID', 'NAME', 'PROPERTY_FILE']);
}