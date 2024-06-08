<?php
const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;

use Bitrix\Main\Application;
use Teaching\CourseCompletion;

$response = [];
$response['request'] = $request = Application::getInstance()->getContext()->getRequest()->getValues();
$fields = [];
foreach ($request['fields'] as $row_id => $row){
    if($row['was_on_course']=='true'){
        $fields[$row_id]['UF_WAS_ON_COURSE'] = 1;
        $fields[$row_id]['UF_FAILED'] = 0;
        $fields[$row_id]['UF_IS_COMPLETE'] = 0;
        $fields[$row_id]['UF_POINTS'] = false;
        if($row['is_complete']=='true') {
            $fields[$row_id]['UF_IS_COMPLETE'] = 1;
            $fields[$row_id]['UF_FAILED'] = false;
            $fields[$row_id]['UF_COMPLETED_TIME'] = date('d.m.Y H:i:s');
        } else {
            $fields[$row_id]['UF_FAILED'] = 1;
            $fields[$row_id]['UF_COMPLETED_TIME'] = date('d.m.Y H:i:s');
        }
        if(empty($row['points'])){
            $fields[$row_id]['UF_POINTS'] = false;
        }else{
            $fields[$row_id]['UF_POINTS'] = (int)$row['points'];
        }
    } else {
        $fields[$row_id]['UF_WAS_ON_COURSE'] = 0;
        $fields[$row_id]['UF_FAILED'] = 0;
        $fields[$row_id]['UF_IS_COMPLETE'] = 0;
        $fields[$row_id]['UF_POINTS'] = false;
        $fields[$row_id]['UF_COMPLETED_TIME'] = date('d.m.Y H:i:s');
        $fields[$row_id]['UF_DIDNT_COM'] = 1;
    }
    $completions = new CourseCompletion();

    $completions->update($row_id, $fields[$row_id]);
    if ( $fields[$row_id]['UF_IS_COMPLETE'] == 1 )
        \Helpers\Pdf::generateCertFromCompletionId($row_id);
    if ( $fields[$row_id]['UF_DIDNT_COM'] == 1 ) {
        $completion = $completions->find($row_id);
        if(check_full_array($completion)) {
            $enrollments = new \Teaching\Enrollments();
            $enroll = current($enrollments->get(['UF_USER_ID' =>$completion['UF_USER_ID'], 'UF_SHEDULE_ID' => $completion['UF_SHEDULE_ID']]));
            if ( check_full_array($enroll) ) {
                $enrollments->setNotCome($enroll['ID']);
            }
        }
    } else {
        $completion = $completions->find($row_id);
        if (check_full_array($completion)) {
            $enrollments = new \Teaching\Enrollments();
            $enroll = current($enrollments->get(['UF_USER_ID' =>$completion['UF_USER_ID'], 'UF_SHEDULE_ID' => $completion['UF_SHEDULE_ID']]));
            if (check_full_array($enroll)&&$enroll['UF_DIDNT_COM']==1) {
                $enrollments->unsetNotCome($enroll['ID']);
            }
        }
    }
    if ( $fields[$row_id]['UF_FAILED'] == 1 ) {
        $completion = $completions->find($row_id);
        if(check_full_array($completion)) {
            $enrollments = new \Teaching\Enrollments();
            $enroll = current($enrollments->get(['UF_USER_ID' =>$completion['UF_USER_ID'], 'UF_SHEDULE_ID' => $completion['UF_SHEDULE_ID']]));
            if ( check_full_array($enroll) ) {
                $enrollments->setFailed($enroll['ID']);
            }
        }
    } else {
        $completion = $completions->find($row_id);
        if (check_full_array($completion)) {
            $enrollments = new \Teaching\Enrollments();
            $enroll = current($enrollments->get(['UF_USER_ID' =>$completion['UF_USER_ID'], 'UF_SHEDULE_ID' => $completion['UF_SHEDULE_ID']]));
            if (check_full_array($enroll)&&$enroll['UF_FAILED']==1) {
                $enrollments->unsetFailed($enroll['ID']);
            }
        }
    }
    $response['success'] = true;
}
$response['fields'] = $fields;
echo json_encode($response);

