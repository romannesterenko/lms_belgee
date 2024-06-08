<?php
use Bitrix\Main\Localization\Loc;
/** @var array $arResult */
/** @var array $arParams */
foreach ($arResult['ITEMS'] as &$item){
    $item['STATUS'] = Loc::getMessage('NOT_ENROLLED');
    $item['POINTS'] = "-";
    $enrolls = (new \Teaching\Enrollments())->get(['UF_USER_ID' => $arParams['USER_ID'], 'UF_COURSE_ID' => $item['ID']]);
    $completions = (new \Teaching\CourseCompletion())->get(['UF_USER_ID' => $arParams['USER_ID'], 'UF_COURSE_ID' => $item['ID']]);

    $status = \Models\Course::getStatus($item['ID'], $arParams['USER_ID']);
    if($status == 'expired'){
        $item['STATUS'] = 'Просрочен';
        continue;
    }
    if($status == 'expired_date') {
        $item['STATUS'] = 'Требуется повторное прохождение';
        continue;
    }

    foreach ($enrolls as $enroll){
        if($enroll['ID']>0){
            $item['STATUS'] = Loc::getMessage('ENROLLED')." ".(string)$enroll['UF_DATE'];
        }
    }
    $item['MAX_POINTS'] = false;
    foreach ($completions as $completion){
        if($completion['ID']>0) {
            if(!$completion['UF_COMPLETED']) {
                if($item['PROPERTIES']['COURSE_TYPE']['VALUE_ENUM_ID']!=5){
                    if($item['PROPERTIES']['COURSE_TYPE']['VALUE_ENUM_ID']==125){
                        $item['MAX_POINTS'] = "/".\Teaching\Tests::getMaxPointsByCourse($item['ID']);
                    } else {
                        if(check_full_array($item['PROPERTIES']['SCORM']))
                            $item['MAX_POINTS'] = "/100";
                        else
                            $item['MAX_POINTS'] = "/".\Teaching\Tests::getMaxPointsByCourse($item['ID']);
                    }
                }
                $item['STATUS'] = Loc::getMessage('NOT_COMPLETED');
                $item['POINTS'] = $completion['UF_POINTS'];
            }
        }
    }
}