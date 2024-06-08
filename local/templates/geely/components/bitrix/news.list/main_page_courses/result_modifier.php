<?php
/** @var array $arResult */
$enrollments = new \Teaching\Enrollments();
$approved_enrolls = [];
foreach ((new \Teaching\CourseCompletion())->get(["UF_USER_ID" => $USER->GetID(), 'UF_IS_COMPLETE' => false, 'UF_DIDNT_COM' => false, 'UF_FAILED' => false]) as $enroll){
    $approved_enrolls[$enroll['UF_COURSE_ID']] = $enroll;
}
/*foreach ($enrollments->getApprovedEnrolls() as $enroll){
    $approved_enrolls[$enroll['UF_COURSE_ID']] = $enroll;
}*/
$new_items = [];
foreach ($arResult['ITEMS'] as &$ITEM){
    if(!check_full_array($approved_enrolls[$ITEM['ID']]))
        continue;
    if($approved_enrolls[$ITEM['ID']]['UF_IS_FREE_SCHEDULE'] == 1){
        if(!empty($approved_enrolls[$ITEM['ID']]['UF_DATE']))
            $ITEM['PROPERTIES']['BEGIN_DATE']['VALUE'] = \Helpers\DateHelper::getHumanDate($approved_enrolls[$ITEM['ID']]['UF_DATE'], 'd F');
        else
            $ITEM['PROPERTIES']['BEGIN_DATE']['VALUE'] = GetMessage("FREE_VISIT");
    } else {
        $schedule = \Teaching\SheduleCourses::getById($approved_enrolls[$ITEM['ID']]['UF_SHEDULE_ID']);
        $current_schedule = array_shift($schedule);
        $ITEM['PROPERTIES']['BEGIN_DATE']['VALUE'] = \Helpers\DateHelper::getHumanDate($current_schedule['PROPERTIES']['BEGIN_DATE'], 'd F');
        $ITEM['PROPERTIES']['END_DATE']['VALUE'] = \Helpers\DateHelper::getHumanDate($current_schedule['PROPERTIES']['END_DATE'], 'd F Y');
    }
    $new_items[] = $ITEM;

}
$arResult['ITEMS'] = $new_items;