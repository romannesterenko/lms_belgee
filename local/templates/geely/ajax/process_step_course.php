<?php

use Bitrix\Main\Application;
use Teaching\CourseCompletion;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$common_completions = new CourseCompletion();
$completion = $common_completions->getByCourseAndUser((int)$_REQUEST['user_id'], (int)$_REQUEST['course_id']);
$step = $request['action']=='prev'?--$completion['UF_CURR_STEP']:++$completion['UF_CURR_STEP'];
$step = $request['action']=='back_from_test'?1:$step;
if ($step > $completion['UF_ALL_STEPS'])
    $step = $completion['UF_ALL_STEPS'];
$common_completions->setCurrentStep($completion['ID'], $step);
echo json_encode(['success' => true]);