<?php
/** @var array $arResult */
$schedule_id = (int)$_REQUEST['id'];

$schedules = \Teaching\SheduleCourses::getById($schedule_id);
$arResult['SCHEDULE'] = $schedules[$schedule_id];
$arResult['SCHEDULE']['ENDED'] = time()>strtotime($schedules[$schedule_id]['PROPERTIES']['END_DATE']);
$arResult['SCHEDULE']['ALLOW_CANCEL'] = true;
if($schedules[$schedule_id]['PROPERTIES']['NOT_UNENROLL_DATE'])
    $arResult['SCHEDULE']['ALLOW_CANCEL'] = \Teaching\SheduleCourses::isAllowCancelApplication($schedule_id);;
//$arResult['SCHEDULE']['ALLOW_CANCEL'] = time()<strtotime($schedules[$schedule_id]['PROPERTIES']['NOT_UNENROLL_DATE']);
$confirmation = new \Teaching\CourseCompletion();
foreach ($arResult['rows'] as &$row){
    $row['UF_CREATED_AT'] = !empty($row['UF_CREATED_AT'])?(int)$row['UF_CREATED_AT']:(int)time();
    $row['USER'] = \Helpers\UserHelper::getList(['ID'=>$row['UF_USER_ID']], ['NAME', 'LAST_NAME', 'PERSONAL_PROFESSION', 'PERSONAL_PHOTO']);
}
$arResult['COURSE'] = \Teaching\Courses::getById($arResult['SCHEDULE']['PROPERTIES']['COURSE']);
$role_ids = [];
if(check_full_array($arResult['COURSE']['PROPERTIES']['ROLES'])){
    foreach ($arResult['COURSE']['PROPERTIES']['ROLES'] as $role)
        $role_ids[] = $role['VALUE'];
}
$arResult['ROLES'] = \Teaching\Roles::getGenitiveForm($role_ids);