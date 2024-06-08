<?php use Integrations\Scorm;
use Teaching\CourseCompletion;
use Teaching\Courses;

define("NO_KEEP_STATISTIC", true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;
$response = [];
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getValues();
$response['request'] = $request;
$response['course'] = Courses::getById((int)$request['course_id']);
$completions = new CourseCompletion();
$response['scorm_string'] = $completions->getScormCompletionString($response['course']['ID'], (int)$request['user_id']);
$scorm_completions_array = explode(';', $response['scorm_string']);
if(check_full_array($scorm_completions_array)){
    $part = (int)end($scorm_completions_array)??0;
    if($request['part']!='all'&&(int)$request['part']>0)
        $part = ((int)$request['part'])-1;
    $response['data'] = (new Scorm())->getData((int)$request['user_id'], $response['course']['ID'], $part);

    $response['data']['scorm_completion_part'] = $part+1;
    unset($response['data']['suspend_data']);
}
echo json_encode($response);

