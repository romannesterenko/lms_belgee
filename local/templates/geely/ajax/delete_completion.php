<?php

use Bitrix\Main\Application;
use Teaching\CourseCompletion;
use Teaching\Enrollments;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$request = Application::getInstance()->getContext()->getRequest()->getValues();
if($request['id']>0) {
    $completion = new CourseCompletion();
    $current_completion = current($completion->get(['ID' => $request['id']]));
    if($current_completion['ID']>0) {
        $enrolls = new Enrollments();
        $enrollment = current($enrolls->get(['UF_IS_APPROVED' => 1, 'UF_USER_ID' => $current_completion['UF_USER_ID'], 'UF_SHEDULE_ID' => $current_completion['UF_SHEDULE_ID']]));
        if($enrollment['ID']>0)
            $enrolls->delete($enrollment['ID']);
        $completion->delete($current_completion['ID']);
    }
}
echo json_encode(['success'=>true, 'request' => $request]);

