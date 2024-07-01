<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__).'/../../..');
$_SERVER["REMOTE_ADDR"] = $_SERVER["DOCUMENT_ROOT"];
$_SERVER["REQUEST_METHOD"] = 'GET';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$end_date = ConvertDateTime(date('d.m.Y', strtotime('yesterday')), "YYYY-MM-DD");
$start_date = ConvertDateTime(date('d.m.Y', strtotime('-2 days')), "YYYY-MM-DD");
$filter = [
    '!PROPERTY_COURSE' => false,
    '>=PROPERTY_END_DATE' => $start_date.' 00:00:01',
    '<=PROPERTY_END_DATE' => $end_date.' 23:59:59',
];
$list = \Teaching\SheduleCourses::getAllArray($filter, ['ID']);
foreach ( $list as $item ) {
    $completions = (new \Teaching\CourseCompletion())->get(['UF_SHEDULE_ID' => $item['ID'], 'UF_IS_COMPLETE' => false, "UF_FAILED" => false, "UF_DIDNT_COM" => false]);
    if (check_full_array($completions)) {
        foreach ($completions as $completion) {
            (new \Teaching\CourseCompletion())->setDidntComCourse($completion['ID']);
            (new \Teaching\CourseCompletion())->setFromCronUpdate($completion['ID']);
            if($completion['UF_SHEDULE_ID']>0){
                $enroll = current(
                    (new \Teaching\Enrollments())->get(
                        [
                            'UF_USER_ID' => $completion['UF_USER_ID'],
                            'UF_SHEDULE_ID' => $completion['UF_SHEDULE_ID'],
                            'UF_DIDNT_COM' => false,
                        ]
                    )
                );
                if($enroll['ID']>0){
                    (new \Teaching\Enrollments())->setNotCome($enroll['ID']);
                }
            }
        }
    }
}