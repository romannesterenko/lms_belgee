<?php
/** @var array $arResult */
foreach ($arResult['ITEMS'] as &$ITEM){
    if($ITEM['PROPERTIES']['COURSE_TYPE']['VALUE_ENUM_ID']==6){
        $ITEM['PROPERTIES']['BEGIN_DATE']['VALUE'] = GetMessage("FREE_VISIT");
    }else{
        $schedules = \Teaching\SheduleCourses::getByCourse($ITEM['ID']);
        $schedule = end($schedules);
        $ITEM['PROPERTIES']['BEGIN_DATE']['VALUE'] = \Helpers\DateHelper::getHumanDate($schedule['PROPERTIES']['BEGIN_DATE'], 'd F');
        $ITEM['PROPERTIES']['END_DATE']['VALUE'] = \Helpers\DateHelper::getHumanDate($schedule['PROPERTIES']['END_DATE'], 'd F Y');
    }
}