<?php
/** @var array $arResult */
$course_ids = [];

foreach ($arResult['rows'] as $row) {
    $course_ids[] = $row['UF_COURSE_ID'];
}
$courses = \Teaching\Courses::getList(['ID'=>$course_ids, 'ACTIVE' => 'ALL'], ['ID', 'NAME', 'CODE', 'PROPERTY_COURSE_FORMAT']);
$new = [];
foreach ($arResult['rows'] as $key => &$row) {
    if($courses[$row['UF_COURSE_ID']]['ID']>0) {
        $row['STATUS'] = \Models\Course::getStatus($row['UF_COURSE_ID']);
        $row['COURSE_NAME'] = $courses[$row['UF_COURSE_ID']]['NAME'];
        $row['COURSE_LINK'] = '/courses/'.$courses[$row['UF_COURSE_ID']]['CODE'].'/';
        $row['COURSE_TYPE'] = $courses[$row['UF_COURSE_ID']]['PROPERTY_COURSE_FORMAT_VALUE'];
        $row['UF_ORIGINAL_DATE'] = $row['UF_DATE'];
        $row['UF_DATE'] = \Helpers\DateHelper::getHumanDate($row['UF_DATE'], 'd F Y');
        $new[] = $row;
    }
}
$arResult['rows'] = $new;