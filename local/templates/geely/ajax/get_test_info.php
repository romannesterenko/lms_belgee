<?php use Teaching\Courses;

define("NO_KEEP_STATISTIC", true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;
$response = [];
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getValues();
$response['request'] = $request;
$response['course'] = Courses::getById((int)$request['course_id']);
$response['test'] = current(\Teaching\Tests::getTestByCourse($response['course']['ID'], ['ID', 'NAME', 'PROPERTY_POINTS']));
if(!$response['test'])
    $response['message'] = "Для курса ".$response['course']['NAME']." еще не был создан тест. Укажите количество баллов для прохождения теста в поле ниже для корректного его автоматического создания";
else {
    $response['message'] = "Для курса " . $response['course']['NAME'] . " найден тест. Вы можете изменить количество баллов для прохождения";
}
echo json_encode($response);

