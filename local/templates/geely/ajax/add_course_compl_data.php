<?php
const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Teaching\CourseCompletion;

$response = [];
$response['request'] = $request = Application::getInstance()->getContext()->getRequest()->getValues();
$response['success'] = false;
$fields['UF_COMMENT'] = $request['coment'];
if($request['was_on_course']=='on'){
    $fields['UF_WAS_ON_COURSE'] = 1;
    $fields['UF_FAILED'] = 0;
    $fields['UF_IS_COMPLETE'] = 0;
    $fields['UF_POINTS'] = false;
    if($request['completed_course']=='on'){
        $fields['UF_IS_COMPLETE'] = 1;
        $fields['UF_FAILED'] = false;
        $fields['UF_COMPLETED_TIME'] = date('d.m.Y H:i:s');
        if(empty($request['points'])){
            $response['message'] = Loc::getMessage('NEED_SET_POINTS');
        }else{
            $fields['UF_POINTS'] = (int)$request['points'];
        }
    } else {
        /*if(!empty($request['points'])&&(int)$request['points']>0){
            $response['message'] = Loc::getMessage('NEED_SET_COMPLETED_FOR_SETTING_POINTS');
        } else {*/
            $fields['UF_POINTS'] = (int)$request['points'];
            $fields['UF_IS_COMPLETE'] = false;
            $fields['UF_FAILED'] = 1;
        //}
    }
} else {
    if($request['completed_course']=='on'){
        $response['message'] = Loc::getMessage('NEED_SET_WAS_ON_COURSE_FOR_SETTING_COMPLETE');
    }else{
        if(!empty($request['points'])&&(int)$request['points']>0){
            $response['message'] = Loc::getMessage('NEED_SET_WAS_AND_COMPL_FOR_POINTS');
        }else{
            $fields['UF_WAS_ON_COURSE'] = false;
            $fields['UF_IS_COMPLETE'] = false;
            $fields['UF_FAILED'] = false;
            $fields['UF_DIDNT_COM'] = 1;
        }
    }
}
if(empty($response['message'])){
    $completions = new CourseCompletion();
    $completions->update($request['completion_id'], $fields);
    if ( $fields['UF_IS_COMPLETE'] == 1 )
        \Helpers\Pdf::generateCertFromCompletionId($request['completion_id']);
    if ( $fields['UF_DIDNT_COM'] == 1 ) {
        $completion = $completions->find($request['completion_id']);
        if(check_full_array($completion)) {
            $enrollments = new \Teaching\Enrollments();
            $enroll = current($enrollments->get(['UF_USER_ID' =>$completion['UF_USER_ID'], 'UF_SHEDULE_ID' => $completion['UF_SHEDULE_ID']]));
            if ( check_full_array($enroll) ) {
                $enrollments->setNotCome($enroll['ID']);
            }
        }
    } else {
        $completion = $completions->find($request['completion_id']);
        if (check_full_array($completion)) {
            $enrollments = new \Teaching\Enrollments();
            $enroll = current($enrollments->get(['UF_USER_ID' =>$completion['UF_USER_ID'], 'UF_SHEDULE_ID' => $completion['UF_SHEDULE_ID']]));
            if (check_full_array($enroll)&&$enroll['UF_DIDNT_COM']==1) {
                $enrollments->unsetNotCome($enroll['ID']);
            }
        }
    }
    if ( $fields['UF_FAILED'] == 1 ) {
        $completion = $completions->find($request['completion_id']);
        if(check_full_array($completion)) {
            $enrollments = new \Teaching\Enrollments();
            $enroll = current($enrollments->get(['UF_USER_ID' =>$completion['UF_USER_ID'], 'UF_SHEDULE_ID' => $completion['UF_SHEDULE_ID']]));
            if ( check_full_array($enroll) ) {
                $enrollments->setFailed($enroll['ID']);
            }
        }
    } else {
        $completion = $completions->find($request['completion_id']);
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
echo json_encode($response);

