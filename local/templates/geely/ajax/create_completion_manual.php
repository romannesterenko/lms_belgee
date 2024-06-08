<?php

use Bitrix\Main\Application;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$success = true;
if($request['course_id']>0){
    if($request['user_id']>0) {
        if ( $request['course_date'] ) {
            $completions = new \Teaching\CourseCompletion();
            $request['course_date'] = date('d.m.Y', strtotime($request['course_date']));
            if (\Teaching\Courses::isFreeSheduleCourse($request['course_id'])) {
                $item['UF_USER_ID'] = $enroll['UF_USER_ID'] = $request['user_id'];
                $item['UF_DATE'] = $enroll['UF_DATE'] = date('d.m.Y', strtotime($request['course_date']));
                $item['UF_COURSE_ID'] = $request['course_id'];
                $item['UF_POINTS'] = $request['points'];
                $item['UF_COMPLETED_DATE'] = $enroll['UF_CREATED_AT'] = $item['UF_DATE'] . ' 12:00:00';
                $item['UF_DATE_CREATE'] = date('d.m.Y H:i:s');
                $item['UF_TOTAL_ATTEMPTS'] = 1;
                $item['UF_MANUAL_ADDED'] = 1;
                $enroll['UF_IS_APPROVED'] = $item['UF_IS_COMPLETE'] = $item['UF_FROM_EXPORT'] = $item['UF_VIEWED'] = $item['UF_WAS_ON_COURSE'] = true;
            } else {
                $item['UF_USER_ID'] = $enroll['UF_USER_ID'] = $request['user_id'];
                $item['UF_SHEDULE_ID'] = $enroll['UF_SHEDULE_ID'] = \Teaching\SheduleCourses::findOrCreateExportSchedule($request['course_id'], $request['course_date']);
                $item['UF_DATE'] = $enroll['UF_DATE'] = date('d.m.Y', strtotime($request['course_date']));
                $item['UF_COURSE_ID'] = $request['course_id'];
                $item['UF_COMPLETED_DATE'] = $enroll['UF_CREATED_AT'] = $item['UF_DATE'] . ' 12:00:00';
                $item['UF_DATE_CREATE'] = date('d.m.Y H:i:s');
                $item['UF_TOTAL_ATTEMPTS'] = 1;
                if($request['pretest_points']>0){
                    $item['UF_PRETEST_PROCESS'] = 1;
                    $item['UF_PRETEST_POINTS'] = $request['pretest_points'];
                }
                $item['UF_POINTS'] = $request['points'];
                $item['UF_MANUAL_ADDED'] = 1;
                $enroll['UF_IS_APPROVED'] = $item['UF_IS_COMPLETE'] = $item['UF_FROM_EXPORT'] = $item['UF_VIEWED'] = $item['UF_WAS_ON_COURSE'] = true;
                (new \Teaching\Enrollments())->add($enroll);
            }
            if(\Teaching\Courses::isPaid($request['course_id'])&&$request['certificate']>0){
                \Models\Certificate::activate($request['certificate'], $request['user_id']);
            }
            $completions->add($item);
            $completion = current($completions->get(['UF_MANUAL_ADDED' => 1, 'UF_DATE' => $item['UF_DATE'], 'UF_COURSE_ID' => $item['UF_COURSE_ID'], 'UF_USER_ID' => $item['UF_USER_ID']]));
            if (check_full_array($completion))
                \Helpers\Pdf::generateCertFromCompletionId($completion['ID']);
        } else {
            $success = false;
            $message = "Укажите дату";
        }
    } else {
        $success = false;
        $message = "Укажите пользователя";
    }
} else {
    $success = false;
    $message = "Курс не указан";
}
echo json_encode(['success' => $success, 'request' => $request, 'completion' => $item, 'enroll' => $enroll, 'message' => $message]);

