<?php
/** @var array $arResult */
$course_ids = [];
$schedule_ids = [];
foreach ($arResult['rows'] as $row) {
    $course_ids[] = $row['UF_COURSE_ID'];
    if($row['UF_SHEDULE_ID']>0)
        $schedule_ids[] = $row['UF_SHEDULE_ID'];
}
if(check_full_array($schedule_ids))
    $schedules = \Teaching\SheduleCourses::getById($schedule_ids);

$courses = \Teaching\Courses::getList(['ID'=>$course_ids, 'ACTIVE' => 'ALL'], ['ID', 'NAME', 'CODE', 'PROPERTY_COURSE_FORMAT']);
foreach ($arResult['rows'] as &$row) {
    $row['COURSE_NAME'] = $courses[$row['UF_COURSE_ID']]['NAME'];
    $row['COURSE_LINK'] = '/courses/'.$courses[$row['UF_COURSE_ID']]['CODE'].'/';
    $row['COURSE_TYPE'] = $courses[$row['UF_COURSE_ID']]['PROPERTY_COURSE_FORMAT_VALUE']??'Offline';
    $row['UF_DATE'] = check_full_array($schedules[$row['UF_SHEDULE_ID']])?\Helpers\DateHelper::getHumanDate($schedules[$row['UF_SHEDULE_ID']]['PROPERTIES']['BEGIN_DATE'], 'd F Y'):\Helpers\DateHelper::getHumanDate($row['UF_DATE'], 'd F Y');
}